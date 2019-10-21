<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class UpdateInfoModel extends BaseModel
{
    public $table = 'UpdateInfo';
    public $primaryKey = 'Id';
    public $timestamps = false;

    public static function GetPageList()
    {
        return self::orderBy('Id', 'desc')->limit(1)->first();
    }


    /**
     * @func 根据$id查找数据
     * @param $id
     * @return mixed
     */
    public static function GetBId(int $id)
    {
        return self::where('Id', $id)->first();
    }


}
