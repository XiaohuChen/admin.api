<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class CoinModel extends BaseModel
{
    public $table = 'Coin';
    public $primaryKey = 'Id';

    public $timestamps = false;

    public static function GetPageList(int $count)
    {
        return self::orderBy('Id', 'desc')->paginate($count);
    }


    public static function get_by_id(int $id)
    {
        $data = self::where('Id', $id)->first();
        return $data;
    }

    /**
     * @func根据id删除数据
     * @param $id
     * @return mixed
     */
    public static function DelById(int $id)
    {
        return self::where('id', $id)->update(['IsDel' => 1]);
    }

}
