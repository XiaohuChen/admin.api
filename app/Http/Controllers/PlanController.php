<?php

namespace App\Http\Controllers;

use App\Exceptions\ArException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PlanController extends Controller
{

    //产品配置
    public function List(){
        $res = DB::table('PlanLevel')->get();
        foreach($res as $key => $item){
            $pIds = json_decode($item->ProductId, true);
            $name = '';
            foreach($pIds as $pid){
                $product = DB::table('Products')->where('Id', $pid)->first();
                if(empty($product)) continue;
                $name .= $product->Name.'  '; 
            }
            $item->Products = $name; 
        }
        return self::returnMsg($res);
    }

    //等级详情
    public function Detail(Request $request){
        $id = intval($request->input('Id'));
        if($id <= 0) throw new ArException(ArException::PARAM_ERROR);
        $data = DB::table('PlanLevel')->where('Id', $id)->first();
        return self::returnMsg($data);
    }

    //更新等级
    public function Edit(Request $request){
        $id = intval($request->input('Id'));
        if($id <= 0) 
            throw new ArException(ArException::SELF_ERROR,'参数错误');
        $rules = [
            'Name' => 'required|string',
            'ContinueTime' => 'required|integer',
            'ProductIds' => 'required|string'
        ];
        $valid = Validator::make($request->all(), $rules,[
            'Name.required' => '请填写等级名称',
            'ContinueTime.required' => '请填写持续轮数',
            'ProductIds' => '请选择连续购买商品'
        ]);
        if($valid->fails())
            return self::errorMsg($valid->errors()->first());
        $data = $valid->validated();
        $pids = json_decode($data['ProductIds'], true);
        if(!is_array($pids)) throw new ArException(ArException::SELF_ERROR,'商品参数错误');
        DB::table('PlanLevel')->where('Id', $id)->update([
            'Name' => $data['Name'],
            'ContinueTime' => $data['ContinueTime'],
            'ProductId' => $data['ProductIds']
        ]);
        return self::returnMsg();
    }

    //添加等级
    public function Add(Request $request){
        $rules = [
            'Name' => 'required|string',
            'ContinueTime' => 'required|integer',
            'ProductIds' => 'required|string'
        ];
        $valid = Validator::make($request->all(), $rules,[
            'Name.required' => '请填写等级名称',
            'ContinueTime.required' => '请填写持续轮数',
            'ProductIds' => '请选择连续购买商品'
        ]);
        if($valid->fails())
            return self::errorMsg($valid->errors()->first());
        $data = $valid->validated();
        $pids = json_decode($data['ProductIds'], true);
        if(!is_array($pids)) throw new ArException(ArException::SELF_ERROR,'商品参数错误');
        $max = DB::table('PlanLevel')->max('Level');
        $lv = $max + 1;
        DB::table('PlanLevel')->insert([
            'Level' => $lv,
            'Name' => $data['Name'],
            'ContinueTime' => $data['ContinueTime'],
            'ProductId' => $data['ProductIds']
        ]);
        return self::returnMsg();
    }
}
