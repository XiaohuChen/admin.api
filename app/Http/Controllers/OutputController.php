<?php

namespace App\Http\Controllers;

use App\Exceptions\ArException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OutputController extends Controller
{

    //产品配置
    public function List(Request $request){
        $data = DB::table('RewardRecord');
        if(!empty(trim($request->input('Phone')))){
            $member = DB::table('Members')->where('Phone', $request->input('Phone'))->get()->toArray();
            $mids = array_column($member,'Id');
            $data = $data->whereIn('MemberId', $mids);
        }
        if(!empty($request->input('Type'))) $data = $data->where('Type', $request->input('Type'));
        $res = $data->paginate(intval($request->input('limit')));
        $list = [];
        foreach($res as $item){
            $phone = '';
            $name = '';
            $member = DB::table('Members')->where('Id', $item->MemberId)->first();
            if(!empty($member)){
                $phone = $member->Phone;
                $name = $member->NickName;
            }
            $item->Phone = $phone;
            $item->NickName = $member->NickName;
            $item->Coin = 'USDT';
            $list[] = $item;
        }
        $sumReward = $data->where('Number','>', 0)->sum('Number');
        $res = ['list' => $list, 'total' => $res->total(), 'SumReward' => $sumReward];
        return self::returnMsg($res);
    }
}
