<?php

namespace App\Http\Controllers;

use App\Libraries\base;
use App\Models\FinancingListModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;


class WithdrawController extends Controller
{

    //转出记录
    public function withdrawList(Request $request)
    {
        $count   = (int)trim($request->input('limit')) ? trim($request->input('limit')) : 20;
        $where   = function ($query) use ($request) {
            if ($request->has('keywords') and $request->keywords != '') {
                $key = intval($request->keywords);
                $query->orWhere('Withdraw.Id', '=', $key)->orWhere('Members.Phone', '=', $request->keywords)->orWhere('Withdraw.Remark', '=', $request->keywords);
            }
            if ($request->has('status') and $request->status != '') {
                $query->where('Withdraw.Status', $request->status);
            }
        };
        $sql_str = 'Withdraw.*,Coin.EnName,Coin.Name,Members.NickName,Members.Phone,Coin.Protocol';
        $list    = DB::table('Withdraw')
            ->select(
                DB::raw($sql_str)
            )
            ->leftJoin('Coin', function ($join) {
                $join->on('Withdraw.CoinId', '=', 'Coin.Id');
            })
            ->leftJoin('Members', function ($join) {
                $join->on('Withdraw.MemberId', '=', 'Members.Id');
            })
            ->where($where)
            ->orderBy('Withdraw.Id', 'DESC')
            ->paginate($count);

        if ($list) {
            return self::returnMsg($list, '操作成功', 20000);
        } else {
            return self::returnMsg([], '暂无数据', 20000);
        }
    }


    //获取币种的记录
    public function getWithdrawCoin(Request $request)
    {
        $id = (int)$request->input('id');
        if (empty($id)) {
            return self::returnMsg([], 'id不能为空', '20001');
        }
        $sql_str = 'Withdraw.*,Coin.EnName,Coin.Name,Members.NickName,Members.Phone as MMobile,Coin.Protocol,Coin.RPCUser';
        $list    = DB::table('Withdraw')
            ->select(
                DB::raw($sql_str)
            )
            ->leftJoin('Coin', function ($join) {
                $join->on('Withdraw.CoinId', '=', 'Coin.Id');
            })
            ->leftJoin('Members', function ($join) {
                $join->on('Withdraw.MemberId', '=', 'Members.Id');
            })
            ->where('Withdraw.id', '=', $id)
            ->first();
        if ($list) {
            return self::returnMsg($list, '操作成功', 20000);
        } else {
            return self::returnMsg([], '暂无数据', 20000);
        }
    }


    //币种审核待处理
    public function waitProcess(Request $request)
    {
        $id   = (int)trim($request->input('id'));
        $type = (int)trim($request->input('type'));

        $Withdraw = DB::table('Withdraw')->where('Id', $id)->first();
        $result = 0;
        $thrift = new base('CoinService', 'CoinService');
        $thrift->to_Tsorket(env('THRIFT_IP'), env('THRIFT_PORT'));//默认测试服务器
        $thrift->transport->open();
        switch ($type) {
            case 1:
                $result = $thrift->client->AuthWithdraw($id, 1);
                break;
            case 2:
                $result = $thrift->client->AuthWithdraw($id, 0);
                break;
            case 3:
                $result = $thrift->client->AuthWithdraw($id, 2);
                break;
            default:
                return self::returnMsg([], '类型参数错误', 20003);
        }


    }

}
