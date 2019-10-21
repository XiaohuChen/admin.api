<?php

namespace App\Http\Controllers;

use App\Exceptions\ArException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CommunityController extends Controller
{

    //产品配置
    public function List(){
        $res = DB::table('CommunityLevelSetting')->get();
        return self::returnMsg($res);
    }

    //获取等级
    public function Detail(Request $request){
        $id = intval($request->input('Id'));
        $res = DB::table('CommunityLevelSetting')->where('Id', $id)->first();
        return self::returnMsg($res);
    }

    public function Edit(Request $request){
        $id = intval($request->input('Id'));
        if($id <= 0) 
            throw new ArException(ArException::SELF_ERROR,'参数错误');
        $rules = [
            'Level' => 'required|integer',
            'Name' => 'required|string',
            'InviteNumber' => 'required|numeric',
            'Achive' => 'required|numeric',
            'HasLevel' => 'required|numeric',
            'PlanLevel' => 'required|numeric',
            'World' => 'required|numeric',
            'Ratio' => 'required|numeric'
        ];
        $valid = Validator::make($request->all(), $rules,[
            'Level.required' => '等级不能为空',
            'Name.required' => '等级名称不得为空',
            'InviteNumber.required' => '直推数量不得为空',
            'Achive.required' => '请填写伞下业绩',
            'HasLevel.required' => '请填写5代内包含的等级',
            'PlanLevel.required' => '请选择预约等级',
            'World.required' => '请选择是否能全球分红',
            'Ratio.required' => '请填写社区收益'
        ]);
        if($valid->fails())
            return self::errorMsg($valid->errors()->first());
        $data = $valid->validated();
        DB::table('CommunityLevelSetting')->where('Id', $id)->update([
            'Level' => $data['Level'],
            'Name' => $data['Name'],
            'InviteNumber' => $data['InviteNumber'],
            'Achive' => $data['Achive'],
            'HasLevel' => $data['HasLevel'],
            'PlanLevel' => $data['PlanLevel'],
            'World' => $data['World'],
            'Ratio' => $data['Ratio']
        ]);
        return self::returnMsg();
    }

    //添加社区等级
    public function Add(Request $request){
        $rules = [
            'Level' => 'required|integer',
            'Name' => 'required|string',
            'InviteNumber' => 'required|numeric',
            'Achive' => 'required|numeric',
            'HasLevel' => 'required|numeric',
            'PlanLevel' => 'required|numeric',
            'World' => 'required|numeric',
            'Ratio' => 'required|numeric'
        ];
        $valid = Validator::make($request->all(), $rules,[
            'Level.required' => '等级不能为空',
            'Name.required' => '等级名称不得为空',
            'InviteNumber.required' => '直推数量不得为空',
            'Achive.required' => '请填写伞下业绩',
            'HasLevel.required' => '请填写5代内包含的等级',
            'PlanLevel.required' => '请选择预约等级',
            'World.required' => '请选择是否能全球分红',
            'Ratio.required' => '请填写社区收益'
        ]);
        if($valid->fails())
            return self::errorMsg($valid->errors()->first());
        $data = $valid->validated();
        $has = DB::table('CommunityLevelSetting')->where('Level', $data['Level'])->first();
        if(!empty($has)) throw new ArException(ArException::SELF_ERROR,'等级'.$data['Level'].'已存在');
        DB::table('CommunityLevelSetting')->insert([
            'Level' => $data['Level'],
            'Name' => $data['Name'],
            'InviteNumber' => $data['InviteNumber'],
            'Achive' => $data['Achive'],
            'HasLevel' => $data['HasLevel'],
            'PlanLevel' => $data['PlanLevel'],
            'World' => $data['World'],
            'Ratio' => $data['Ratio']
        ]);
        return self::returnMsg();
    }

}
