<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class NoticeModel extends BaseModel
{
    public $table = 'Notice';

    public $timestamps = false;

//    public static function GetPageList(int $count){
//        return self::where('IsDel','<>',1)->orderBy('Id','desc')->select('id','Title','Content','AddTime','IsRead')->paginate($count);
//    }


    public static function GetPageList(int $count, $where = [])
    {
        return self::where('IsDel', '<>', 1)->where($where)->orderBy('Id', 'desc')->select('Id', 'Title', 'IsDel', 'AddTime', 'IsRead')->paginate($count);
    }

    /**
     * @func 根据$id查找数据
     * @param $id
     * @return mixed
     */
    public static function GetBId(int $id)
    {
        return self::where('id', $id)->where('IsDel', '<>', 1)->select('id', 'Title', 'Content', 'AddTime', 'IsRead')->first();
    }

    /**
     * Notes:根据id查数据
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
        return self::where('id', $id)->update(['IsDel' => 1]);
    }


}
