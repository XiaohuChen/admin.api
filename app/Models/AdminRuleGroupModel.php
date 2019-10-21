<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class AdminRuleGroupModel extends BaseModel
{
    public $table = 'AdminRuleGroup';
    public $primaryKey = 'Id';

    public static function get_id_by_name($name)
    {
        $data = self::whereIn('Name', $name)->pluck('Id')->toArray();
        $data = implode(',', $data);
        return $data;
    }

    public static function get_name_by_id($id)
    {
        $data = self::whereIn('Id', $id)->pluck('Name')->toArray();
        $data = implode(',', $data);
        return $data;
    }

    public static function get_by_id($id)
    {
        $data = self::where('Id', $id)->first();
        return $data;
    }
}
