<?php
/**
 * Created by PhpStorm.
 * User: ChenJulong
 * Date: 2019/8/27
 * Time: 10:28
 */


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class FinancingListModel extends BaseModel
{

    public $timestamps = false;

    /**
     * Notes:写日志
     */
    public static function WriteLog($MemberId, $CoinId, $CoinName, $Money, $Balance, $Mold, $MoldTitle, $Remark)
    {
        $table = $MemberId % 20;
        if ($table < 10) $table = '0' . $table;
        $table  = 'FinancingList_' . $table;
        $sqlmap = [
            'MemberId'  => $MemberId,
            'CoinId'    => $CoinId,
            'CoinName'  => $CoinName,
            'Money'     => $Money,
            'Balance'   => $Balance,
            'Mold'      => $Mold,
            'MoldTitle' => $MoldTitle,
            'Remark'    => $Remark,
            'AddTime'   => time(),
        ];
        return DB::table($table)->insert($sqlmap);
    }

    public static function get_mold_by_call_index($callIndex)
    {
        $data = DB::table('FinancingMold')
            ->where('call_index', $callIndex)
            ->first();
        return $data;
    }


}
