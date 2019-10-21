<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class HelpModel extends Model
{
    public $table = 'Help';

    public $timestamps = false;

    public static function GetPageList(int $count,$where){
        return self::where($where)->orderBy('Id','desc')->paginate($count);
    }


    /**
     * @func 根据$id查找数据
     * @param $id
     * @return mixed
     */
    public static function GetBId(int $id){
        return self::where('id', $id)->first();
    }


    /**
     * @func根据id删除数据
     * @param $id
     * @return mixed
     */
    public static function DelById(int $id){
        return self::where('id',$id)->update(['IsDel'=>1]);
    }




}
