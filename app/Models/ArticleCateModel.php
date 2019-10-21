<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ArticleCateModel extends BaseModel
{
    public $table = 'ArticleCate';
    public $primaryKey = 'Id';
    public $timestamps = false;

    /**
     * @func 获取分类列表
     * @param int $count
     * @return mixed
     */
    public static function GetPageList(int $count)
    {
        return self::where('IsDel', '<>', 1)->orderBy('Id', 'desc')->select()->paginate($count);
    }


    /**
     * @func 获取全部分类列表
     * @param int $count
     * @return mixed
     */
    public static function GetList()
    {
        return self::where('IsDel', '<>', 1)->orderBy('Id', 'desc')->get(['Id as id', 'Pid as pid', 'Name as title'])->toArray();
    }


    /**
     * @func 根据分类pid获取分类的名称
     */
    public static function GetCateName(int $pid)
    {
        if ($pid == 0) {
            return '顶级分类';
        } else {
            $Name = self::where('Id', $pid)->value('Name');
            if ($Name) {
                return $Name;
            } else {
                return '没有找到';
            }
        }
    }


    /**
     * @func 根据$id查找数据
     * @param $id
     * @return mixed
     */
    public static function GetBId(int $id)
    {
        return self::where('Id', $id)->where('IsDel', '<>', 1)->first();
    }


    /**
     * @func根据id删除数据
     * @param $id
     * @return mixed
     */
    public static function DelById(int $id)
    {
        return self::where('Id', $id)->update(['IsDel' => 1]);
    }

    /**
     * @func 根据id获取相同分类文章的文章条数
     */
    public static function getPidListCount(int $id)
    {
        return self::where('Pid', $id)->where('IsDel', '0')->count();
    }


}
