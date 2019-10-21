<?php

namespace App\Http\Controllers;

use App\Models\AdminRuleGroupModel;

use Illuminate\Http\Request;
use App\Models\AdminRulesModel as AdminRules;

use App\Libraries\Tree;
use App\Libraries\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RuleController extends Controller
{
    //权限列表
    public function List(Request $request)
    {
        $type = trim($request->input('type'));
        $cate = new Category(new AdminRules, ['Id', 'ParentId', 'Name', 'CName']);
        $data = $cate->getList(0, 'id');
        if ($type == 'tree') {
            $list = [];
            foreach ($data as $k => $v) {
                $tmp          = $v->toArray();
                $tmp['check'] = false;
                $tmp['Id']    = intval($tmp['Id']);
                $list[]       = $tmp;
            }
            $tree = new Tree();
            $data = $tree->list_to_tree($list, 'Id', 'ParentId', 'Child', 0, true);
        }
        return self::returnMsg($data);
    }

    //修改权限
    public function Edit(Request $request)
    {
        $rules = [
            'Id'     => 'required|integer|min:1',
            'Name'   => 'required',
            'Rule'   => 'required',
            'Parent' => 'required|integer',
            'IsLog'  => 'required|integer',
        ];
        $v     = Validator::make($request->all(), $rules);
        if ($v->fails())
            return self::errorMsg($v->errors());
        $sqlmap = $v->validated();

        $data = AdminRules::where('Id', $sqlmap['Id'])->first();
        if (!isset($data))
            return self::errorMsg('没有找到该权限');

        $data->Name     = $sqlmap['Name'];
        $data->Rule     = $sqlmap['Rule'];
        $data->ParentId = $sqlmap['Parent'];
        $data->IsLog    = $sqlmap['IsLog'];
        try {
            $data->save();
            return self::successMsg();
        } catch (\Exception $exception) {
            return self::errorMsg($exception->getMessage());
        }
    }

    //添加权限
    public function Add(Request $request)
    {
        $rules = [
            'Name'   => 'required',
            'Rule'   => 'required',
            'Parent' => 'required|integer',
            'IsLog'  => 'required|integer',
        ];
        $v     = Validator::make($request->all(), $rules);
        if ($v->fails())
            return self::errorMsg($v->errors());
        $sqlmap = $v->validated();

        $data           = new AdminRules();
        $data->Name     = $sqlmap['Name'];
        $data->Rule     = $sqlmap['Rule'];
        $data->ParentId = $sqlmap['Parent'];
        $data->IsLog    = $sqlmap['IsLog'];
        try {
            $data->save();
            return self::successMsg();
        } catch (\Exception $exception) {
            return self::errorMsg($exception->getMessage());
        }
    }

    //删除权限
    public function Delete(Request $request)
    {
        $id   = (int)$request->input('Id');
        $data = AdminRules::where('Id', $id)->first();
        if (!isset($data))
            return self::errorMsg('未找到该权限规则');
        try {
            $data->delete();
            return self::successMsg();
        } catch (\Exception $exception) {
            return self::errorMsg($exception->getMessage());
        }
    }

    //权限组
    public function Group(Request $request)
    {
        $id = (int)$request->input('id');
        if (!empty($id)) {
            return self::returnMsg(AdminRuleGroupModel::where('Id', $id)->first());
        }
        $groups    = AdminRuleGroupModel::get();
        $groupList = [];
        foreach ($groups as $group) {
            $groupList[] = [
                'Id'      => $group->Id,
                'Name'    => $group->Name,
                'IsClose' => $group->IsClose,
            ];
        }
        return self::returnMsg($groupList);
    }

    //编辑权限组
    public function EditGroup(Request $request)
    {
        $rules = [
            'Id'      => 'required|integer|min:1',
            'IsClose' => 'required|integer',
            'Name'    => 'required',
            'Rules'   => 'required|array',
        ];
        $v     = Validator::make($request->all(), $rules);
        if ($v->fails())
            return self::errorMsg($v->errors());
        $sqlmap = $v->validated();

        $data = AdminRuleGroupModel::get_by_id($sqlmap['Id']);
        if (!isset($data))
            return self::errorMsg('未找到该权限组');

        $data->Name    = $sqlmap['Name'];
        $data->IsClose = $sqlmap['IsClose'];
        $data->Rules   = join(',', array_filter($sqlmap['Rules']));

        try {
            $data->save();
            return self::successMsg();
        } catch (\Exception $exception) {
            return self::errorMsg($exception->getMessage());
        }
    }

    //新建权限组
    public function AddGroup(Request $request)
    {
        $rules = [
            'IsClose' => 'required|integer',
            'Name'    => 'required',
            'Rules'   => 'required|array',
        ];
        $v     = Validator::make($request->all(), $rules);
        if ($v->fails())
            return self::errorMsg($v->errors());
        $sqlmap = $v->validated();

        $data          = new AdminRuleGroupModel();
        $data->Name    = $sqlmap['Name'];
        $data->IsClose = $sqlmap['IsClose'];
        $data->Rules   = join(',', array_filter($sqlmap['Rules']));
        $data->AddTime = time();

        try {
            $data->save();
            return self::successMsg();
        } catch (\Exception $exception) {
            return self::errorMsg($exception->getMessage());
        }
    }

    //删除权限组
    public function DelGroup(Request $request)
    {
        $id   = (int)$request->input('Id');
        $data = AdminRuleGroupModel::get_by_id($id);
        if (!isset($data))
            return self::errorMsg('未找到该权限组');
        try {
            $data->delete();
            return self::successMsg();
        } catch (\Exception $exception) {
            return self::errorMsg($exception->getMessage());
        }

    }
}
