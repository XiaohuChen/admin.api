<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class MembersModel extends BaseModel
{
    public $table = 'Members';

    public $timestamps = false;

    public static function GetPageList(int $count, $where)
    {
        return self::where($where)
            ->leftJoin('Members as a', 'Members.ParentId', '=', 'a.Id')
            ->select('Members.*', 'a.Phone as ParentPhone')
            ->orderBy('Members.Id', 'desc')->paginate($count);
    }


    /**
     * @func 根据$id查找数据
     * @param $id
     * @return mixed
     */
    public static function GetBId(int $id)
    {
        return self::where('Id', '=', $id)->first();
    }


}
