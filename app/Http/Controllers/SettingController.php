<?php

namespace App\Http\Controllers;

use App\Exceptions\ArException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function InviteList(){
        $list = DB::table('InviteRewardSetting')->get();
        return self::returnMsg($list);
    }

    //修改邀请收益
    public function InviteEdit(Request $request){
        $data = $request->input('data');
        DB::beginTransaction();
        try{
            foreach($data as $item){
                DB::table('InviteRewardSetting')
                    ->where('Id', $item['Id'])
                    ->update(['Ratio' => $item['Ratio']]);
            }
            DB::commit();
        } catch(ArException $e){
            DB::rollBack();
            throw new ArException(ArException::SELF_ERROR, '修改失败-101');
        } catch(\Exception $e){
            DB::rollBack();
            throw new ArException(ArException::SELF_ERROR, '修改失败');
        }
        return self::returnMsg();
    }

    //全球分红和回购比例
    public function World(){
        $data = DB::table('RatioSetting')->first();
        return self::returnMsg($data);
    }

    //编辑全球分红和回购比例
    public function WorldEdit(Request $request){
        $rules = [
            'WorldRatio' => 'required|numeric',
            'BackRatio' => 'required|numeric',
        ];
        $valid = Validator::make($request->all(), $rules,[
            'WorldRatio.required' => '请填写等级名称',
            'BackRatio.required' => '请填写持续轮数',
        ]);
        if($valid->fails())
            return self::errorMsg($valid->errors()->first());
        $data = $valid->validated();
        if(bccomp($data['WorldRatio'], 1, 10) > 0)
            throw new ArException(ArException::SELF_ERROR,'分红比例不能大于1');
        if(bccomp($data['BackRatio'], 1, 10) > 0)
            throw new ArException(ArException::SELF_ERROR,'回购比例不能大于1');
        DB::table('RatioSetting')->update([
            'WorldRatio' => $data['WorldRatio'],
            'BackRatio' => $data['BackRatio']
        ]);
        return self::returnMsg();
    }

    public function Plan(){
        $data = DB::table('SystemSetting')->select('PlanNumber','PassNumber')->first();
        return self::returnMsg($data);
    }

    public function PlanEdit(Request $request){
        $rules = [
            'PlanNumber' => 'required|numeric',
            'PassNumber' => 'required|numeric',
        ];
        $valid = Validator::make($request->all(), $rules,[
            'PlanNumber.required' => '请填写每日预约上限',
            'PassNumber.required' => '请填写每日放行上限',
        ]);
        if($valid->fails())
            return self::errorMsg($valid->errors()->first());
        $data = $valid->validated();
        if(bccomp($data['PlanNumber'], 0, 10) < 0)
            throw new ArException(ArException::SELF_ERROR,'预约数量能小于1');
        if(bccomp($data['PassNumber'], 0, 10) < 0)
            throw new ArException(ArException::SELF_ERROR,'放行数量能小于1');
        DB::table('SystemSetting')->update([
            'PlanNumber' => $data['PlanNumber'],
            'PassNumber' => $data['PassNumber']
        ]);
        return self::returnMsg();
    }

}
