<?php
/**
 * Created by PhpStorm.
 * User: ChenJulong
 * Date: 2019/9/26
 * Time: 14:41
 */


namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class AdminLogModel extends BaseModel
{
    public $table = 'AdminLog';
    public $timestamps = false;

    public static function GetPageList($where, $count)
    {
        self::where($where)
            ->join('AdminUser', 'AdminLog.Admin', '=', 'AdminUser.Id')
            ->select('AdminLog.*', 'AdminUser.Name as AdminName')
            ->orderBy('AdminLog.Id', 'DESC')->paginate($count);
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
