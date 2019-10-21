<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class AdminUserModel extends BaseModel
{
    public $table = 'AdminUser';


    public static function GetByName($name)
    {
        return self::where('Name', '=', $name)->first();
    }


    public static function GetPageList(int $count)
    {
        return self::orderBy('Id', 'asc')
            ->paginate($count);
    }

    public static function GetById(int $Id)
    {
        $data = self::where('Id', $Id)->first();
        return $data;
    }

    public function getRolesItemAttribute()
    {
        $groupIds = explode(',', $this->RuleGroup);
        $groups   = AdminRuleGroupModel::whereIn('Id', $groupIds)->get();

        $ruleIds = [];
        foreach ($groups as $group) {
            if ($group->Rules == '*') return '*';
            $rules   = explode(',', $group->Rules);
            $ruleIds = array_merge($ruleIds, $rules);
        }
        $rules    = AdminRulesModel::whereIn('Id', array_filter($ruleIds))->get();
        $ruleList = [];
        foreach ($rules as $rule) {
            $ruleList[] = $rule->Rule;
        }
        return $ruleList;
    }
}
