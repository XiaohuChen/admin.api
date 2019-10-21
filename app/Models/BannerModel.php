<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class BannerModel extends BaseModel
{
    public $table = 'Banner';

    public $timestamps = false;
    public $primaryKey = 'Id';

    public static function GetPageList(int $count, $where = [])
    {
        return self::where('IsDel', '<>', 1)->where($where)->orderBy('Id', 'desc')->paginate($count);
    }


    /**
     * @func 根据$id查找数据
     * @param $id
     * @return mixed
     */
    public static function get_by_id(int $id)
    {
        $data = self::where('Id', $id)->where('IsDel', 0)->first();
        return $data;
    }


    /**
     * @func根据id删除数据
     * @param $id
     * @return mixed
     */
    public static function DelById(int $id)
    {
        //return self::where('id',$id)->delete();
        return self::where('Id', $id)->update(['IsDel' => 1]);
    }


}
