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
