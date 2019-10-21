<?php

namespace App\Http\Controllers;

use App\Exceptions\ArException;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{

    //产品配置
    public function List(){
        $res = DB::table('Products')->get();
        return self::returnMsg($res);
    }

    //产品详情
    public function Detail(Request $request){
        $id = intval($request->input('Id'));
        if($id <= 0) throw new ArException(ArException::SELF_ERROR,'参数错误');
        $product = DB::table('Products')->where('Id', $id)->first();
        if(empty($product)) throw new ArException(ArException::SELF_ERROR,'产品不存在');
        return self::returnMsg($product);
    }

    //更新产品
    public function Edit(Request $request){
        $id = intval($request->input('Id'));
        if($id <= 0) 
            throw new ArException(ArException::SELF_ERROR,'参数错误');
        $rules = [
            'IsClose' => 'required|integer',
            'Name' => 'required|string',
            'NeedLevel' => 'required|numeric',
            'Number' => 'required|numeric',
            'Ratio' => 'required|numeric',
            'Sort' => 'numeric'
        ];
        $valid = Validator::make($request->all(), $rules,[
            'IsClose.required' => '请选择是否开启',
            'Name.required' => '产品名称不得为空',
            'NeedLevel.required' => '请选择预约等级',
            'Number.required' => '投资金额不得为空',
            'Ratio.required' => '收益比例不得为空',
        ]);
        if($valid->fails())
            return self::errorMsg($valid->errors()->first());
        $data = $valid->validated();
        if(bccomp($data['Ratio'], 1, 10) > 0)
            throw new ArException(ArException::SELF_ERROR,'收益比例不能大于1');

        DB::table('Products')->where('Id', $id)->update([
            'Name' => $data['Name'],
            'NeedLevel' => $data['NeedLevel'],
            'Number' => $data['Number'],
            'Ratio' => $data['Ratio'],
            'IsClose' => $data['IsClose'],
            'Sort' => empty($data['Sort']) ? 0 : intval($data['Ratio'])
        ]);
        return self::returnMsg();
    }

    public function Add(Request $request){
        $rules = [
            'IsClose' => 'required|integer',
            'Name' => 'required|string',
            'NeedLevel' => 'required|numeric',
            'Number' => 'required|numeric',
            'Ratio' => 'required|numeric',
            'Sort' => 'numeric'
        ];
        $valid = Validator::make($request->all(), $rules,[
            'IsClose.required' => '请选择是否开启',
            'Name.required' => '产品名称不得为空',
            'NeedLevel.required' => '请选择预约等级',
            'Number.required' => '投资金额不得为空',
            'Ratio.required' => '收益比例错误',
        ]);
        if($valid->fails())
            return self::errorMsg($valid->errors()->first());
        $data = $valid->validated();
        if(bccomp($data['Ratio'], 1, 10) > 0)
            throw new ArException(ArException::SELF_ERROR,'收益比例不能大于1');

        DB::table('Products')->insert([
            'Name' => $data['Name'],
            'NeedLevel' => $data['NeedLevel'],
            'Number' => $data['Number'],
            'Ratio' => $data['Ratio'],
            'IsClose' => $data['IsClose'],
            'Sort' => empty($data['Sort']) ? 0 : intval($data['Ratio']),
            'AddTime' => time()
        ]);
        return self::returnMsg();
    }

}
