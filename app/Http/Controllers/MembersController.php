<?php

namespace App\Http\Controllers;

use App\Libraries\base;
use App\Models\AdminRuleGroupModel;
use App\Models\CoinModel;
use App\Models\FinancingListModel;
use App\Models\MembersModel;
use App\Models\MembersModel as Members;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\MembersCoinModel as MemberCoin;
use Illuminate\Support\Facades\Validator;

class MembersController extends Controller
{

    /**
     * Notes:修改用户交易备注码
     */
    public function memberRemark(Request $request)
    {
        $Id     = $request->input('Id');
        $Remark = $request->input('Remark');

        if (DB::table('Members')->where('Id', $Id)->update(['Remark' => $Remark]))
            return self::returnMsg('操作成功', '操作成功');
        return self::returnError(20001, '操作失败');
    }

    /**
     * Notes:为会员增加一个币种
     */
    public function addCoin(Request $request)
    {
        $rules = [
            'MemberId' => 'required|integer|min:1',
            'CoinId'   => 'required|integer|min:1',
        ];
        $v     = Validator::make($request->all(), $rules);
        if ($v->fails())
            return self::errorMsg($v->errors());
        $sqlmap = $v->validated();

        $coin = CoinModel::get_by_id($sqlmap['CoinId']);
        if (!isset($coin))
            return self::errorMsg('未找到该币种信息');

        $memberCoin = MemberCoin::get_by_memberId_coinId($sqlmap['MemberId'], $sqlmap['CoinId']);
        if (isset($memberCoin))
            return self::errorMsg('该用户已经拥有该币种的钱包信息了');

        $memberCoin->CoinId   = $sqlmap['CoinId'];
        $memberCoin->CoinName = $coin->EnName;
        $memberCoin->MemberId = $sqlmap['MemberId'];

        try {
            $memberCoin->save();
            return self::successMsg();
        } catch (\Exception $exception) {
            return self::errorMsg($exception->getMessage());
        }
    }

    /**
     * @func获取用户会员列表
     */
    public function membersList(Request $request)
    {
        $where = function ($query) use ($request) {
            //筛选查询关键字
            if ($request->has('keywords') and $request->keywords != '') {
                $keywords = "%" . $request->keywords . "%";
                $query->orWhere('Members.Nick', 'like', $keywords)->orWhere('Members.Id', 'like', $keywords)
                    ->orWhere('Members.Phone', 'like', $keywords);
            }
            if ($request->has('IsBan') and $request->IsBan != '' and $request->IsBan < 100) {
                $query->where('Members.IsBan', $request->IsBan);
            }
            if ($request->has('IsFreeze') and $request->IsFreeze != '' and $request->IsFreeze < 100) {
                $query->where('Members.IsFreeze', $request->IsFreeze);
            }
        };

        $bannerlist = Members::GetPageList($request->get('count'), $where);
        return self::returnMsg($bannerlist, '', 20000);
    }


    /**
     * @func查看我的下级
     */
    public function subList(Request $request)
    {
        $where      = function ($query) use ($request) {
            //筛选查询关键字
            if ($request->has('ParentId') and $request->ParentId != '') {
                $query->where('Members.ParentId', '=', $request->ParentId);
            }
        };
        $bannerlist = Members::GetPageList($request->get('count'), $where);
        return self::returnMsg($bannerlist);
    }


    /**
     * @func查看我的持币
     */
    public function holdCoin(Request $request)
    {

        $mid = (int)trim($request->input('mid'));
        if (empty($mid))
            return self::returnMsg([], 'id不能为空', 20003);

        $memberCoinList = MemberCoin::GetPageList($request->get('count'), $mid);
        return self::returnMsg($memberCoinList);
    }


    /**
     * @func修改余额
     */
    public function memberCoinUpdate(Request $request)
    {
        $rules = [
            'mcid'  => 'required|integer|min:1',
            'money' => 'required|numeric',
        ];
        $v     = Validator::make($request->all(), $rules);
        if ($v->fails())
            return self::errorMsg($v->errors());
        $sqlmap = $v->validated();

        $memberCoin_arr = MemberCoin::GetBId($sqlmap['mcid']);
        if (!isset($memberCoin_arr))
            return self::errorMsg('没有找到用户该币种信息');

        $coin = CoinModel::get_by_id($memberCoin_arr->CoinId);
        if (!isset($coin))
            return self::errorMsg('没有找到该币种信息');

        if ($coin->EnName == 'IA') {
            //IA币需要单独记录
            $res = $this->dealIaUpdate($memberCoin_arr, $sqlmap['money'], $request);
            return $res;
        } else {
            $memberCoin_arr->Money = bcadd($memberCoin_arr->Money, $sqlmap['money'], 10);
            $mold                  = FinancingListModel::get_mold_by_call_index('admin_member_coin_update');
            if ($memberCoin_arr->Money < 0)
                return self::errorMsg('用户余额不足');

            DB::beginTransaction();
            try {
                $memberCoin_arr->save();
                FinancingListModel::WriteLog($memberCoin_arr->MemberId, $memberCoin_arr->CoinId, $coin->EnName, $sqlmap['money'],
                    $memberCoin_arr->Money, $mold->id, '后台修改余额', '后台修改余额记账');
                DB::commit();
                return self::successMsg();
            } catch (\Exception $exception) {
                DB::rollBack();
                return self::errorMsg($exception->getMessage());
            }
        }
    }

    //币种审核待处理
    private function dealIaUpdate($memberCoin, $Money, $request)
    {
        $members             = DB::table('Members')->where('Id', $memberCoin['MemberId'])->first();
        $IAMainAddressConfig = DB::table('IAMainAddressConfig')->first();

        if (!$members->WalletAccount || !$members->WalletPrivateKey) return self::returnError(20001, '该用户未绑定钱包私钥或地址');
        /**
         * 金额为负则为减少
         */
        if ($memberCoin['Money'] <= ($Money * -1)) return self::returnError(20001, '金额不足');

        if ($Money > 0) {
            //增加金额，主地址转到私有地址
            $privateKey = $IAMainAddressConfig->PrivateKey;
            $account    = $members->WalletAccount;
        } else {
            //减少金额，私有地址转到主地址
            $privateKey = $members->WalletPrivateKey;
            $account    = $IAMainAddressConfig->Account;
        }
        $thrift = new base('CoinServer', 'IIAService');
        $thrift->to_Tsorket(env('THRIFT_IP_COIN'), env('THRIFT_PORT_COIN'));//默认测试服务器
        $thrift->transport->open();
//        var_dump($thrift->IIA->GetBalance($members->WalletAccount));
        $result = $thrift->IIA->Send($privateKey, $account, abs($Money), rand(1, 10000));
        $thrift->transport->close();
        $result = json_decode($result, true);
        //dd($result);
        if ((int)$result['status'] == 1) {
            $logSql = [
                'Uri'   => $request->url(),
                'Name'  => '后台操作IA币余额变动,Id为' . $members->Id . '的用户IA币余额变动' . $Money,
                'Ip'    => $request->ip(),
                'Admin' => $request->get('uid'),
                'Time'  => time(),
            ];
            DB::table('AdminLog')->insert($logSql);
            if ($Money < 0) {
                //减少币，需要写日志和减少MemberCoin
                DB::beginTransaction();
                try {
                    FinancingListModel::WriteLog($members->Id, $memberCoin['CoinId'], $memberCoin['CoinName'], $Money, bcadd($memberCoin['Money'], $Money, 10),
                        2, '后台修改用户余额', '后台修改用户余额记账');
                    DB::table('MemberCoin')->where('MemberId', $members->Id)->where('CoinId', $memberCoin['CoinId'])->increment('Money', $Money);
                    DB::commit();
                    return self::returnMsg('操作成功', '操作成功');
                } catch (\Exception $exception) {
                    DB::rollBack();
                    return self::returnError(20001, $exception->getMessage());
                }
            }
            return self::returnMsg([], '操作成功，请等待区块转账', 20000);
        } else {

            $result['privateKey'] = $privateKey;
            $result['account']    = $account;
            $result['Money']      = $Money;
            return self::returnMsg($result, $result['msg'], 20001);
        }
    }


    /**
     * @func修改锁定余额
     */
    public function memberCoinLockMoney(Request $request)
    {
        $rules = [
            'mcid'  => 'required|integer|min:1',
            'money' => 'required|numeric',
        ];
        $v     = Validator::make($request->all(), $rules);
        if ($v->fails())
            return self::errorMsg($v->errors());
        $sqlmap = $v->validated();

        $memberCoin_arr = MemberCoin::GetBId($sqlmap['mcid']);
        if (!isset($memberCoin_arr))
            return self::errorMsg('没有找到用户该币种信息');

        $coin = CoinModel::get_by_id($memberCoin_arr->CoinId);
        if (!isset($coin))
            return self::errorMsg('没有找到该币种信息');

        if ($coin->EnName == 'IA')
            return self::errorMsg('IA币不允许锁定');

        $memberCoin_arr->LockMoney = bcadd($memberCoin_arr->LockMoney, $sqlmap['money'], 10);
        $memberCoin_arr->Money     = bcsub($memberCoin_arr->Money, $sqlmap['money'], 10);
        $mold                      = FinancingListModel::get_mold_by_call_index('admin_member_coin_lock');
        if ($memberCoin_arr->Money < 0 || $memberCoin_arr->LockMoney < 0)
            return self::errorMsg('用户余额不足');
        DB::beginTransaction();
        try {
            $memberCoin_arr->save();
            FinancingListModel::WriteLog($memberCoin_arr->MemberId, $memberCoin_arr->CoinId, $coin->EnName, $sqlmap['money'] * -1,
                $memberCoin_arr->Money, $mold->id, '后台修改余额', '后台修改余额记账');
            DB::commit();
            return self::successMsg();
        } catch (\Exception $exception) {
            DB::rollBack();
            return self::errorMsg($exception->getMessage());
        }
    }


    /**
     * 禁用会员账户
     */
    public function membersStatus(Request $request)
    {
        $type       = (int)$request->input('type');
        $id         = (int)($request->input('id'));
        $member_arr = Members::GetBId($id);
        if (!$member_arr) {
            return self::returnMsg([], '对不起，没有找到用户', 20001);
        }
        if ($type > 0) {
            //冻结用户，不能提现
            if ($member_arr['IsFreeze'] > 0) {
                $result = Members::where(['Id' => $id])->update(['IsFreeze' => 0]);
            } else {
                $result = Members::where(['Id' => $id])->update(['IsFreeze' => 1]);
            }
        } else {
            //锁定用户
            if ($member_arr['Status'] > 0) {
                $result = Members::where(['Id' => $id])->update(['Status' => 0]);
            } else {
                $result = Members::where(['Id' => $id])->update(['Status' => 1]);
            }
        }

        if ($result) {
            return self::returnMsg([], '操作成功', 20000);
        } else {
            return self::returnMsg([], '操作失败', 20011);
        }
    }


    /**
     * 根据id获取我持币的某一条记录
     */
    public function getCoinId(Request $request)
    {
        $cid = (int)trim($request->input('cid'));
        if (empty($cid))
            return self::returnMsg([], 'id不能为空', 20003);

        $result = MemberCoin::GetBId($cid);
        if ($result)
            return self::returnMsg($result, '获取成功', 20000);
        return self::returnMsg([], '操作失败', 20011);
    }


}
