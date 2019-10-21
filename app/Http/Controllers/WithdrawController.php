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
        $sql_str = 'Withdraw.*,Coin.EnName,Coin.Name,Members.NickName,Members.Phone,Members.Remark as MembersRemark,Coin.Protocol';
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
        if ($Withdraw->CoinName == 'IA') {
            if (!$Withdraw || $Withdraw->Status !== '0') return self::returnError(20001, '该区块转账已经处理完毕');
            switch ($type) {
                case 3:
                    //直接处理
                    if (!$Withdraw || $Withdraw->Status !== '0') return self::returnError(20001, '该区块转账已经处理完毕');

                    DB::beginTransaction();
                    try {
                        $members = DB::table('Members')->where('Id', $Withdraw->MemberId)->first();

                        $thrift  = new base('CoinServer', 'CoinService');
                        $thrift->to_Tsorket(env('THRIFT_IP_COIN'), env('THRIFT_PORT_COIN'));//默认测试服务器
                        $thrift->transport->open();
                        $result = $thrift->IIA->Send($members->WalletPrivateKey, $Withdraw->Address, $Withdraw->Money, $Withdraw->Remark);
                        $thrift->transport->close();
                        $res = json_decode($result, true);

                        if ($res['status'] == 1) {
                            DB::table('Withdraw')->where('Id', $id)->update(['Status' => 2, 'ProcessTime' => time(), 'Hash' => $res['hash']]);
                            DB::table('MemberCoin')->where('MemberId', $Withdraw->MemberId)->where('CoinId', $Withdraw->CoinId)
                                ->decrement('Forzen', $Withdraw->Money);
                            DB::commit();
                            return self::returnMsg('操作成功', '操作成功');
                        } else {
                            $data['thrift']           = $res;
                            $data['WalletPrivateKey'] = $members->WalletPrivateKey;
                            $data['Address']          = $Withdraw->Address;
                            $data['Money']            = $Withdraw->Money;
                            $data['Remark']           = $Withdraw->Remark;
                            DB::rollBack();
                            return self::returnMsg($data, $res['msg'], 20001);
                        }
                    } catch (\Exception $exception) {
                        DB::rollBack();
                        return self::returnError(20001, $exception->getMessage());
                    }
                    break;
                default:
                    //驳回
                    if (!$Withdraw || $Withdraw->Status !== '0') return self::returnError(20001, '该区块转账已经处理完毕');
                    $memberCoin = DB::table('MemberCoin')->where('MemberId', $Withdraw->MemberId)->where('CoinId', $Withdraw->CoinId)->first();
                    $sqlmap     = [
                        'Money'  => bcadd($memberCoin->Money, $Withdraw->Money, 10),
                        'Forzen' => bcsub($memberCoin->Forzen, $Withdraw->Money, 10),
                    ];
                    DB::beginTransaction();
                    try {
                        DB::table('Withdraw')->where('Id', $id)->update(['Status' => -1, 'ProcessTime' => time()]);
                        DB::table('MemberCoin')->where('MemberId', $Withdraw->MemberId)->where('CoinId', $Withdraw->CoinId)->update($sqlmap);
                        FinancingListModel::WriteLog($Withdraw->MemberId, $Withdraw->CoinId, $Withdraw->CoinName, $Withdraw->Money, $sqlmap['Money'],
                            '10', '后台驳回提现转账', '后台驳回提现转账记账');

                        DB::commit();
                        return self::returnMsg('操作成功', '操作成功');
                    } catch (\Exception $exception) {
                        DB::rollBack();
                        return self::returnError(20001, $exception->getMessage());
                    }
                    break;
            }
        } else {
            $result = 0;
            $thrift = new base('CoinService', 'CoinService');
            $thrift->to_Tsorket(env('THRIFT_H5_IP_COIN'), env('THRIFT_H5_PORT_COIN'));//默认测试服务器
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

}
