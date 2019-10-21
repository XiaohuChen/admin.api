<?php

namespace App\Http\Controllers;

use App\Libraries\googleYzm\GoogleAuthenticator;
use App\Models\AdminLogModel;
use App\Models\AdminRuleGroupModel;
use App\Models\AdminUserModel;
use Illuminate\Http\Request;
use App\Models\AdminUserModel as AdminUser;
use App\Exceptions\AdminException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class AdminController extends Controller
{
    /**
     * Note:
     */
    public function adminLogList(Request $request)
    {
        $where      = function ($query) use ($request) {
            //筛选查询关键字
            if ($request->has('keywords') and $request->keywords != '') {
                $key = intval($request->keywords);
                $query->orWhere('AdminLog.Id', '=', $key)->orWhere('AdminLog.Admin', '=', $request->keywords);
            }
        };
        $count      = intval($request->input('limit')) ? intval($request->input('limit')) : 20;
        $bannerlist = AdminLogModel::GetPageList($where, $count);

        return self::returnMsg($bannerlist);

    }

    /**
     * Notes:更换谷歌验证码秘钥
     */
    public function adminGuge(Request $request)
    {
        $user = AdminUser::GetById($request->get('uid'));

        $verification = trim($request->input('yzm'));
        if (empty($verification))
            return self::returnMsg([], '请输入验证码', 20003);

        $google      = new GoogleAuthenticator();
        $checkResult = $google->verifyCode($user->google_secret, $verification, 1);
        if (!$checkResult)
            return self::returnMsg(['code' => $user->google_secret], '验证码错误，请重新输入', 20003);

        if ($request->has('is') && $request->is == 1) {
            //更换验证码
            $Id         = intval($request->get('uid'));
            $google     = new GoogleAuthenticator();
            $new_secret = $google->createSecret();

            if (AdminUserModel::where('Id', $Id)->update(['google_secret' => $new_secret]))
                return self::returnMsg('更新谷歌验证码秘钥成功:' . $new_secret);
            return self::returnError(20001, '操作失败');
        }

        //查看验证码
        return self::returnMsg($user->google_secret, '操作成功');

    }

    //管理员列表
    public function List(Request $request)
    {
        $count = intval($request->input('limit')) > 0 ? intval($request->input('limit')) : 20;

        $admins = AdminUser::GetPageList($count);
        if (empty($admins)) return self::returnList();
        $total = AdminUser::count('Id');
        $list  = [];
        foreach ($admins as $admin) {
            $list[] = [
                'Id'           => $admin->Id,
                'Name'         => $admin->Name,
                'Status'       => $admin->IsDel,
                'Introduction' => $admin->Introduction,
                'Avatar'       => $admin->Avatar,
                'AddTime'      => date('Y-m-d H:i:s', $admin->AddTime),
            ];
        }
        $res['List'] = $list;
        return self::returnList($res, $total);
    }

    //删除管理员
    public function Delete(Request $request)
    {
        $id = (int)$request->input('Id');

        $uid = $request->get('uid');
        if ($id == $uid) return self::returnError(AdminException::PARAMS_ERROR, '不可删除自己!');

        $admin = AdminUser::GetById($id);
        if (empty($admin)) return self::returnMsg([], '没有找到用户', 20003);

        if ($admin['CouldNotDel'] == 1) {
            return self::returnMsg([], '对不起，不能删除超级管理员', 20003);
        }

        $admin->delete();
        return self::returnMsg();
    }

    //添加管理员
    public function addAdmin(Request $request)
    {

        $rules = [
            'name'         => 'required',
            'password'     => 'required|min:6|alpha',
            'ruleGroup'    => 'required|array',
            'introduction' => 'required',
        ];
        $v     = Validator::make($request->all(), $rules);
        if ($v->fails()) return self::returnError(20001, $v->errors());
        $sqlmap = $v->validated();

        $adminUser = AdminUserModel::GetByName($sqlmap['name']);
        if (isset($adminUser)) self::returnError(20001, '当前管理员名称已存在');

        $new_secret               = (new GoogleAuthenticator())->createSecret();
        $adminUser->Name          = $sqlmap['name'];
        $adminUser->Password      = md5($sqlmap['password']);
        $adminUser->AddTime       = time();
        $adminUser->RuleGroup     = AdminRuleGroupModel::get_id_by_name($sqlmap['ruleGroup']);
        $adminUser->Introduction  = $sqlmap['introduction'];
        $adminUser->CouldNotDel   = 0;
        $adminUser->IsDel         = 0;
        $adminUser->google_secret = $new_secret;

        try {
            $result = $adminUser->save();
            return self::returnMsg(['Guge' => $new_secret], '操作成功', 20000);
        } catch (Exception $e) {
            return self::returnError(20001, $e->getMessage());
        }
    }


    //修改管理员
    public function updateAdmin(Request $request)
    {

        $rules = [
            'id'           => 'required|integer',
            'name'         => 'required',
            'password'     => 'required|min:6|alpha',
            'ruleGroup'    => 'required|array',
            'introduction' => 'required',
        ];
        $v     = Validator::make($request->all(), $rules);
        if ($v->fails()) return self::returnError(20001, $v->errors());
        $sqlmap = $v->validated();

        $adminUser = AdminUserModel::GetByName($sqlmap['name']);
        if (isset($adminUser)) return self::returnError(20001, '当前管理员名称已存在');

        $adminUser = AdminUserModel::GetById($sqlmap['id']);
        if (!isset($adminUser)) return self::returnError(20001, '未找到该账号');

        $adminUser->Name         = $sqlmap['name'];
        $adminUser->Password     = md5($sqlmap['password']);
        $adminUser->AddTime      = time();
        $adminUser->RuleGroup    = AdminRuleGroupModel::get_id_by_name($sqlmap['ruleGroup']);
        $adminUser->Introduction = $sqlmap['introduction'];
        try {
            $result = $adminUser->save();
            return self::returnMsg('', '操作成功', 20000);
        } catch (Exception $e) {
            return self::returnError(20001, $e->getMessage());
        }
    }

    //获取一条管理员信息
    public function getAdmin(Request $request)
    {
        $id = (int)trim($request->input('id'));
        if (empty($id))
            return self::returnMsg([], 'id不能为空', 20001);
        $result = AdminUserModel::GetById($id);

        if ($result) {
            $ids                 = explode(',', $result['RuleGroup']);
            $result['rouleName'] = AdminRuleGroupModel::get_name_by_id($ids);

            if ($result['Avatar']) {
                $result['Avatar'] = $this->config['Domain'] . '/' . $result['Avatar'];
            } else {
                $result['Avatar'] = '';
            }

            return self::returnMsg($result, '操作成功', 20000);
        }
        return self::returnMsg([], '操作失败', 20011);
    }

    //获取权限组
    public function ruleList()
    {
        $result = AdminRuleGroupModel::select('Id', 'Name')->get();
        if ($result)
            return self::returnMsg($result, '操作成功', 20000);
        return self::returnMsg([], '权限组暂无数据', 20000);
    }
}
