<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class MembersCoinModel extends BaseModel
{
    public $table = 'MemberCoin';
    public $primaryKey = 'Id';
    public $timestamps = false;

    public static function GetPageList($count, $mid)
    {

        return self::where('MemberId', '=', $mid)
            ->join('Coin', 'MemberCoin.CoinId', '=', 'Coin.Id')
            ->select('Coin.Name', 'MemberCoin.*')
            ->orderBy('MemberCoin.Id', 'desc')->paginate($count);
    }


    /**
     * @func 根据$id查找数据
     * @param $id
     * @return mixed
     */
    public static function GetBId($id)
    {
        return self::where('Id', $id)->first();
    }

    public static function get_by_memberId_coinId($memberId, $coinId)
    {
        $data = self::where('MemberId', $memberId)->where('Id', $coinId)->first();
        return $data;
    }


}
