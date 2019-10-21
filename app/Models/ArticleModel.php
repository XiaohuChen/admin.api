<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ArticleModel extends BaseModel
{
    public $table = 'Article';
    public $primaryKey = 'Id';
    public $timestamps = false;

    public static function GetPageList(int $count, $where)
    {
        return self::where('IsDel', '<>', 1)->where($where)->orderBy('Id', 'desc')->select('Id', 'Cateid', 'Title', 'Content', 'Create_time', 'Update_time', 'IsDel', 'Sort')->paginate($count);
    }


    /**
     * @func 根据$id查找数据
     * @param $id
     * @return mixed
     */
    public static function GetBId(int $id)
    {
        return self::where('id', $id)->where('IsDel', '<>', 1)->first();
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
