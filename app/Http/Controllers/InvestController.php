<?php

namespace App\Http\Controllers;

use App\Exceptions\ArException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvestController extends Controller
{

    //产品配置
    public function List(Request $request){
        $cond = [];
        if(!empty($request->input('State')) && $request->input('State') != -1)
            $cond['State'] = intval($request->input('State'));
        $data = DB::table('MemberProducts')->where($cond);
        if(!empty($request->input('PlanDate'))){
            $date[0] = strtotime($request->input('PlanDate')[0]);
            $date[1] = strtotime($request->input('PlanDate')[1]);
            $data = $data->whereBetween('AddTime', $date);
        }
        if(!empty(trim($request->input('Phone')))){
            $member = DB::table('Members')->where('Phone', $request->input('Phone'))->get()->toArray();
            $mids = array_column($member,'Id');
            $data = $data->whereIn('MemberId', $mids);
        }
        if(!empty($request->input('PassDate'))){
            $date[0] = strtotime($request->input('PassDate')[0]);
            $date[1] = strtotime($request->input('PassDate')[1]);
            $data = $data->whereBetween('PassTime', $date);
        }
        
        $res = $data->paginate($request->input('limit'));
        $list = [];
        foreach($res as $item){
            $phone = '';
            $member = DB::table('Members')->where('Id', $item->MemberId)->first();
            if(!empty($member)) $phone = $member->Phone;
            $item->Phone = $phone;
            //
            $passUser = '系统';
            $admin = DB::table('AdminUser')->where('Id', $item->PassAdminUser)->first();
            if(!empty($admin)) $passUser = $admin->Name;
            $item->PassUser = $passUser;
            $list[] = $item;
        }
        return self::returnMsg(['list' => $list, 'total' => $res->total()]);
    }

    //放行
    public function Pass(Request $request){
        $id = intval($request->input('Id'));
        if($id <= 0) throw new ArException(ArException::PARAM_ERROR);

        $invs = DB::table('MemberProducts')->where('Id', $id)->first();
        if(empty($invs)) throw new ArException(ArException::SELF_ERROR,'不存在此订单');
        if($invs->State != 0) throw new ArException(ArException::SELF_ERROR,'订单状态错误');
        DB::table('MemberProducts')->where('Id', $id)->update(['State' => 3, 'PassAdminUser' => $request->get('uid')]);
        return self::returnMsg();
    }

}
