<?php

namespace App\Http\Controllers;

use App\Exceptions\ArException;
use App\Models\CoinModel;
use App\Models\wv_FinancingListModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class CoinController extends Controller
{

    //批量通过
    public function MultipleReject(Request $request){
        $ids = $request->input('Ids');
        $remark = $request->input('Remark');
        if(empty($remark))
            throw new ArException(ArException::SELF_ERROR,'请填写驳回理由');

        if(!is_array($ids))
            throw new ArException(ArException::PARAM_ERROR);

        DB::beginTransaction();
        try{
            $record = DB::table('Withdraw')->whereIn('Id', $ids)->where('Status', 0)->get();
            foreach($record as $item){
                DB::table('MemberCoin')
                    ->where('MemberId', $item->MemberId)
                    ->where('CoinId', $item->CoinId)
                    ->update([
                        'Forzen' => DB::raw("Forzen - {$item->Money}"),
                        'Money' => DB::raw("Money+{$item->Money}")
                    ]);
            }
            $res = DB::table('Withdraw')
                ->where('Status', 0)
                ->whereIn('Id', $ids)
                ->update([
                    'Remark' => $remark,
                    'Status' => -1
                ]);
            if(!$res)
                throw new ArException(ArException::SELF_ERROR,'更新失败，请稍后再试');
            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            throw new ArException(ArException::SELF_ERROR, $e->getMessage());
        }
        return self::returnMsg();
    }

    //批量通过
    public function MultiplePass(Request $request){
        $ids = $request->input('Ids');
        $hash = $request->input('Hash');
        if(empty($hash))
            throw new ArException(ArException::SELF_ERROR,'请填写Hash');
        if(!is_array($ids))
            throw new ArException(ArException::PARAM_ERROR);
        DB::beginTransaction();
        try{
            $record = DB::table('Withdraw')->whereIn('Id', $ids)->where('Status', 0)->get();
            foreach($record as $item){
                DB::table('MemberCoin')
                    ->where('MemberId', $item->MemberId)
                    ->where('CoinId', $item->CoinId)
                    ->decrement('Forzen', $item->Money);
            }
            $res = DB::table('Withdraw')
                ->where('Status', 0)
                ->whereIn('Id', $ids)
                ->update([
                    'Hash' => $hash,
                    'Status' => 2
                ]);
            if(!$res)
                throw new ArException(ArException::SELF_ERROR,'更新失败，请稍后再试');
            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            throw new ArException(ArException::SELF_ERROR, $e->getMessage());
        }
        return self::returnMsg();
    }

    /**
     * Notes:获取币种列表，用于展示或选择币种
     */
    public function getCoinList(Request $request)
    {
        $data = DB::table('Coin')->get();
        foreach ($data as $v) {
            $arr[$v->Id] = $v->EnName;
        }
        return self::returnMsg($arr);
    }

    /**
     * Notes:获取资金变动类型
     */
    public function financingMoldList(Request $request)
    {
        $data = DB::table('FinancingMold')->get();
        return self::returnMsg($data);
    }

    /**
     * Notes:资金流水信息
     */
    public function financingList(Request $request)
    {
        $where = function ($query) use ($request) {
            //筛选查询关键字
            if ($request->has('keywords') and $request->keywords != '') {
                $query->orWhere('wv_FinancingList.MemberId', '=', intval($request->keywords))->orWhere('Members.Phone', '=', $request->keywords);
            }
            if ($request->has('Mold') and $request->Mold > 0) {
                $query->where('wv_FinancingList.Mold', '=', $request->input('Mold'));
            }
        };

        $bannerlist = wv_FinancingListModel::get_list($where, $request->get('count'));
        return self::returnMsg($bannerlist, '', 20000);
    }

    /**
     * @func获取币种的列表
     */
    public function coinList(Request $request)
    {
        $bannerlist = CoinModel::GetPageList($request->get('count'));
        return self::returnMsg($bannerlist, '', 20000);
    }

    /**
     * @func 新增币种
     */
    public function coinAdd(Request $request)
    {
        $data_field = $this->validateCoin($request);
        if (!$data_field['status']) {
            return self::returnMsg([], $data_field['data'], 20002);
        }
        $domain = '';
        $qiniu = DB::table('QiniuConfig')->first();
        if(!empty($qiniu)) $domain = $qiniu->Domain;
        $data_field['data']['Logo'] = $domain.'/'.$data_field['data']['Logo'];
        $result = CoinModel::insert($data_field['data']);
        if ($result) {
            self::redisFlushAll();
            return self::returnMsg([], '操作成功', 20000);
        } else {
            return self::returnMsg([], '操作失败', 20011);
        }
    }

    /**
     * @func 修改币种
     * @param Request $request
     */
    public function coinUpdate(Request $request)
    {
        $id = (int)$request->input('Id');

        $data_field = $this->validateCoin($request);
        if (!$data_field['status']) {
            return self::returnMsg([], $data_field['data'], 20002);
        }

        $coin = CoinModel::get_by_id($id);
        if (!isset($coin))
            return self::errorMsg('未找到该币种');
        try {
            $domain = '';
            $qiniu = DB::table('QiniuConfig')->first();
            if(!empty($qiniu)) $domain = $qiniu->Domain;
            $data_field['data']['Logo'] = $domain.'/'.$data_field['data']['Logo'];
            //$coin->update($data_field['data']);
            if (CoinModel::where('Id', $id)->update($data_field['data'])) {
                self::redisFlushAll();
                return self::successMsg();
            }
            return self::errorMsg();
        } catch (\Exception $exception) {
            return self::errorMsg($exception->getMessage());
        }

    }

    /**
     * @func获取数据，以及数据验证
     */
    private function validateCoin($request)
    {
        $money_reg           = '/^(\-|\+)?\d+(\.\d+)?$/';
        $sqldata['Protocol'] = trim($request->input('Protocol'));              //协议类型
        if (empty($sqldata['Protocol'])) {
            return ['status' => false, 'data' => '请选择币种协议'];
        }
        $sqldata['Name'] = trim($request->input('Name'));  //币种中文名称
        if (empty($sqldata['Name'])) {
            return ['status' => false, 'data' => '币种中文名称必须填写'];
        }
        $sqldata['EnName'] = trim($request->input('EnName'));        //币种英文名称
        if (empty($sqldata['EnName'])) {
            return ['status' => false, 'data' => '币种英文名称必须填写'];
        }
        $sqldata['FullName'] = trim($request->input('FullName'));      //英文全称
        $image               = trim($request->input('Logo'));      //图片
        if (empty($image)) {
            return ['status' => false, 'data' => '请上传logo图片'];
        }
        $sqldata['Logo']    = trim($request->input('Logo'));
        $sqldata['AddTime'] = time();
        $sqldata['Price']   = (float)trim($request->input('Price'));     //当前市场价格
        if (!empty($sqldata['Price'])) {
            if (!preg_match($money_reg, $sqldata['Price'])) {
                return ['status' => false, 'data' => '市场价格金额错误'];
            }
        }
        $sqldata['Description'] = trim($request->input('Description'));       //简介
        if (!empty($request->input('PublishTime'))) {
            $data    = $request->input('PublishTime');
            $is_date = strtotime($data) ? strtotime($data) : false;
            if ($is_date === false) {
                return ['status' => false, 'data' => '时间格式错误'];
            }
            $sqldata['PublishTime'] = strtotime($data);
        }
        $sqldata['PublishNum'] = (int)trim($request->input('PublishNum'));        //发行数量
        if (!empty($sqldata['PublishNum'])) {
            if (!is_numeric($sqldata['PublishNum'])) {
                return ['status' => false, 'data' => '发行数量请输入数字'];
            }
        }
        $sqldata['CirculationNum'] = (int)trim($request->input('CirculationNum'));    //流通数量
        if (!empty($sqldata['CirculationNum'])) {
            if (!is_numeric($sqldata['CirculationNum'])) {
                return ['status' => false, 'data' => '流通数量请输入数字'];
            }
        }
        $sqldata['CrowdPrice'] = (float)trim($request->input('CrowdPrice'));    //众筹价格
        if (!empty($sqldata['CrowdPrice'])) {
            if (!preg_match($money_reg, $sqldata['CrowdPrice'])) {
                return ['status' => false, 'data' => '众筹价格金额错误'];
            }
        }
        $sqldata['WhitePaper']  = trim($request->input('WhitePaper'));    //白皮书地址
        $sqldata['WebUrl']      = trim($request->input('WebUrl'));        //官网
        $sqldata['Browser']     = trim($request->input('Browser'));       //区块浏览器地址
        $sqldata['WithDrawFee'] = (float)trim($request->input('WithDrawFee'));       //提现手续费
        if (!empty($sqldata['WithDrawFee'])) {
            if (!preg_match($money_reg, $sqldata['WithDrawFee'])) {
                return ['status' => false, 'data' => '提现手续费金额错误'];
            }
        }
        $sqldata['MinWithDrawFee'] = (float)trim($request->input('MinWithDrawFee'));    //最低提现手续费
        if (!empty($sqldata['MinWithDrawFee'])) {
            if (!preg_match($money_reg, $sqldata['MinWithDrawFee'])) {
                return ['status' => false, 'data' => '最低提现手续费金额错误'];
            }
        }
        $sqldata['IsWithDraw']     = trim($request->input('IsWithDraw'));        //是否可提现
        $sqldata['IsRecharge']     = trim($request->input('IsRecharge'));        //是否可充值
        $sqldata['IsAutoWithDraw'] = trim($request->input('IsAutoWithDraw'));    //是否自动处理
        $sqldata['Status']         = trim($request->input('Status'));           //状态，是否启用
        $sqldata['RPCServer']      = trim($request->input('RPCServer'));     //钱包服务器
        $sqldata['RPCUser']        = trim($request->input('RPCUser'));       //钱包账号
        $sqldata['RPCPassword']    = trim($request->input('RPCPassword'));     //钱包密码
        $sqldata['MinWithDraw']    = (float)trim($request->input('MinWithDraw'));       //最低提现数量
        if (!empty($sqldata['MinWithDraw'])) {
            if (!preg_match($money_reg, $sqldata['MinWithDraw'])) {
                return ['status' => false, 'data' => '最低提现数量金额错误'];
            }
        }
        $sqldata['MinRecharge'] = (float)trim($request->input('MinRecharge'));     //最低充值数量
        if (!empty($sqldata['MinRecharge'])) {
            if (!preg_match($money_reg, $sqldata['MinRecharge'])) {
                return ['status' => false, 'data' => '最低充值数量金额错误'];
            }
        }
        $sqldata['MaxWithDraw'] = (float)trim($request->input('MaxWithDraw'));       //最大提现数量
        if (!empty($sqldata['MaxWithDraw'])) {
            if (!preg_match($money_reg, $sqldata['MaxWithDraw'])) {
                return ['status' => false, 'data' => '最大提现数量金额错误'];
            }
        }
        $sqldata['Fixed'] = (int)trim($request->input('Fixed'));
        if (!empty($sqldata['Fixed'])) {
            if (!is_numeric($sqldata['Fixed'])) {
                return ['status' => false, 'data' => '币种保留几位应为整数'];
            }
        }
        $sqldata['RechargeInfo']    = trim($request->input('RechargeInfo')) ?? null;      //充值文字描述
        $sqldata['WithDrawInfo']    = trim($request->input('WithDrawInfo')) ?? null;      //提现文字描述
        $sqldata['Confirms']        = intval($request->input('Confirms')) ?? null;              //网络确认次数
        $sqldata['WalletUrl']       = trim($request->input('WalletUrl')) ?? null;               //钱包链接
        $sqldata['MobileWalletUrl'] = trim($request->input('MobileWalletUrl')) ?? null;
        $sqldata['Sort']            = (int)trim($request->input('Sort')) ?? null;                      //前端显示排序
        if (!empty($sqldata['Sort'])) {
            if (!is_numeric($sqldata['Sort'])) {
                return ['status' => false, 'data' => '排序应为整数'];
            }
        }
        $sqldata['MainAddress'] = trim($request->input('MainAddress')) ?? null;        //主地址
        $sqldata['Ext']         = trim($request->input('Ext')) ?? null;                        //合约地址
        $sqldata['Decimals']    = (int)trim($request->input('Decimals')) ?? null;              //保留长度
        if (!empty($sqldata['Decimals'])) {
            if (!is_numeric($sqldata['Decimals'])) {
                return ['status' => false, 'data' => '保留长度应为整数'];
            }
        }
        return ['status' => true, 'data' => $sqldata];
    }


    /**
     * @func 获取币种
     * @param Request $request
     */
    public function getCoin(Request $request)
    {
        $id = (int)$request->input('id');
        if (empty($id)) {
            return self::returnMsg([], '币种id不能为空', 20001);
        }
        $result = CoinModel::get_by_id($id);

        $result['PublishTime'] = strtotime($result['PublishTime']) ?? '';
        return self::returnMsg($result);

        $is_date = strtotime($result['PublishTime']) ? strtotime($result['PublishTime']) : false;
        if ($is_date === false) {
            $result['PublishTime'] = '';
        } else {
            $result['PublishTime'] = strtotime($result['PublishTime']);
        }
        if ($result) {
            return self::returnMsg($result, '操作成功', 20000);
        } else {
            return self::returnMsg([], '操作失败', 20011);
        }
    }

    /**
     * 选择币种协议
     */
    public function getProtocol(Request $request)
    {
        $list = [
            [
                'id'   => 1,
                'name' => 'ETH',
            ],
            [
                'id'   => 2,
                'name' => 'BTC',
            ],
            [
                'id'   => 3,
                'name' => 'OMNI',
            ],
            [
                'id'   => 4,
                'name' => 'EOS',
            ],
        ];
        return self::returnMsg($list, '操作成功', 20000);
    }
}
