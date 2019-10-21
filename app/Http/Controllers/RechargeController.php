<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class RechargeController extends Controller
{

    //转入记录
    public function rechargeList(Request $request)
    {
        $count   = (int)trim($request->input('limit')) ? trim($request->input('limit')) : 10;
        $where   = function ($query) use ($request) {
            if ($request->has('keywords') and $request->keywords != '') {
                $key          = intval($request->keywords);
                $query->orWhere('Recharge.Id', '=', $key)->orWhere('Members.Phone', '=', $request->keywords);
            }
        };
        $sql_str = 'Recharge.*,Coin.EnName,Coin.Name,Members.NickName,Members.Phone,Coin.Protocol';
        $list    = DB::table('Recharge')
            ->where($where)
            ->leftJoin('Coin', function ($join) {
                $join->on('Recharge.CoinId', '=', 'Coin.Id');
            })
            ->leftJoin('Members', function ($join) {
                $join->on('Recharge.MemberId', '=', 'Members.Id');
            })
            ->orderBy('Recharge.Id', 'desc')
            ->select(
                DB::raw($sql_str)
            )
            ->paginate($count);

        if ($list) {
            return self::returnMsg($list, '操作成功', 20000);
        } else {
            return self::returnMsg([], '暂无数据', 20000);
        }

    }

}
