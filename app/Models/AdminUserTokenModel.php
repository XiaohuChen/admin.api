<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class AdminUserTokenModel extends Model
{
    public $table = 'AdminUserToken';

    //有效时间 小时
    const EXPIRE_HOUR = 2;

    //根据uid查询
    public static function GetByUid(int $uid){
        return self::where('AdminId', $uid)->first();
    }

    //关联AdminUser
    public function admin_user(){

    }
}
