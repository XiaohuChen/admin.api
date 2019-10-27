<?php

namespace App\Http\Controllers;

use App\Libraries\googleYzm\GoogleAuthenticator;
use Illuminate\Http\Request;
use App\Models\AdminUserModel as AdminUser;
use App\Models\AdminUserTokenModel as AdminToken;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{
    public $List = [];

    public function Index(Request $request){}

    public function Statis(Request $request){
        $data = [];
        $today = strtotime(date('Y-m-d'));
        //今日预约
        $plan = DB::table('MemberProducts')->where('AddTime', '>', $today)->where('State', 0)->sum('Number');
        //今日放行
        $pass = DB::table('MemberProducts')->where('AddTime', '>', $today)->where('State', 3)->sum('Number');
        //今日报单
        $pay = DB::table('MemberProducts')->where('AddTime', '>', $today)->where('State', 1)->sum('Number');
        //今日产出
        $sum = DB::table('RewardRecord')->where('AddTime','>', $today)->where('Number','>',0)->sum('Number');
        $data['Today'] = [
            'Plan' => $plan,
            'Pass' => $pass,
            'Pay' => $pay,
            'Sum' => $sum
        ];
        $yesterday = $today - 86400;
        //昨日预约
        $plan = DB::table('MemberProducts')->whereBetween('AddTime', [$yesterday, $today])->where('State', 0)->sum('Number');
        //昨日放行
        $pass = DB::table('MemberProducts')->whereBetween('AddTime', [$yesterday, $today])->where('State', 3)->sum('Number');
        //昨日报单
        $pay = DB::table('MemberProducts')->whereBetween('AddTime', [$yesterday, $today])->where('State', 1)->sum('Number');
        //昨日产出
        $sum = DB::table('RewardRecord')->whereBetween('AddTime', [$yesterday, $today])->where('Number','>',0)->sum('Number');
        $data['Yesterday'] = [
            'Plan' => $plan,
            'Pass' => $pass,
            'Pay' => $pay,
            'Sum' => $sum
        ];
        //总产出
        $plan = DB::table('MemberProducts')->where('State', 0)->sum('Number');
        //总放行
        $pass = DB::table('MemberProducts')->where('State', 3)->sum('Number');
        //总报单
        $pay = DB::table('MemberProducts')->where('State', 1)->sum('Number');
        //总产出
        $sum = DB::table('RewardRecord')->where('Number','>',0)->sum('Number');
        $data['All'] = [
            'Plan' => $plan,
            'Pass' => $pass,
            'Pay' => $pay,
            'Sum' => $sum
        ];
        return self::returnMsg($data);
    }

    //用户信息
    public function userInfo(Request $request){
        $uid   = $request->get('uid');
        $admin = AdminUser::where('Id', $uid)->first();
        $data  = [
            'roles'        => $admin->roles_item,
            'avatar'       => $admin->Avatar,
            'introduction' => $admin->Introduction,
            'name'         => $admin->Name,
        ];
        return self::returnMsg($data, '', 20000);
    }

    //登录
    public function Login(Request $request){
        $name     = trim($request->input('username'));
        $password = trim($request->input('password'));
        $user     = AdminUser::GetByName($name);
        if (empty($user)) return self::returnMsg([], '不存在管理员', 20000);

        /**
         * 谷歌验证码
         */
        $verification = trim($request->input('verification'));
        if (empty($verification)) {
            return self::returnMsg([], '请输入验证码', 20003);
        } else {

        }

        if (md5($password) != $user->Password) return self::returnMsg([], '账号或密码错误', 20000);

        //生成Token
        $token = self::MakeToken($user->Id);
        $has   = AdminToken::GetByUid($user->Id);
        if (empty($has)) {
            $res = DB::table('AdminUserToken')->insert([
                'Token'      => $token,
                'ExpireTime' => (3600 * AdminToken::EXPIRE_HOUR) + time(),
                'FlushTime'  => time(),
                'AdminId'    => $user->Id,
            ]);
        } else {
            $res = DB::table('AdminUserToken')->where('AdminId', $user->Id)->update([
                'Token'      => $token,
                'ExpireTime' => (3600 * AdminToken::EXPIRE_HOUR) + time(),
                'FlushTime'  => time(),
            ]);
        }
        if (empty($res)) return self::returnMsg([], '登录失败，请稍后再试', 20000);
        //Token加密
        $encryptToken = self::TokenEncrypt($user->Id, $token);
        return self::returnMsg(['Token' => $encryptToken], '', 20000);
    }

    //退出登录
    public function Logout(Request $request)
    {
        $uid = $request->get('uid');
        DB::table('AdminUserToken')->where('AdminId', $uid)->delete();
        return self::returnMsg();
    }

}
