<?php
namespace App\Models;
use App\Models\BaseModel;


/**
 * Created by PhpStorm.
 * User: ChenJulong
 * Date: 2019/9/26
 * Time: 17:33
 */
class wv_FinancingListModel extends BaseModel
{
    public $table = 'wv_FinancingList';

    public static function get_list($where, $count)
    {

        $data = self::where($where)
            ->join('Members', 'wv_FinancingList.MemberId', '=', 'Members.Id')
            ->select('wv_FinancingList.*', 'Members.Phone')
            ->orderBy('wv_FinancingList.Id', 'desc')->paginate($count);
        return $data;
    }

}
