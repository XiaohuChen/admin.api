<?php

namespace App\Libraries;


trait Send
{

    /**
     * Notes:返回成功消息
     */
    public static function successMsg($msg = '操作成功', $data = [], $code = 20000)
    {
        return self::returnMsg($data, $msg, $code);
    }

    /**
     * Notes:返回错误信息
     */
    public static function errorMsg($msg = '操作失败', $data = [], $code = 20001)
    {
        return self::returnMsg($data, $msg, $code);
    }

    /**
     * 返回成功
     */
    public static function returnMsg($data = [], $msg = '', $code = 20000)
    {
        return response()->json([
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ]);
    }

    //请求正常，返回列表
    public static function returnList(array $list = [], int $total = 0)
    {
        $data = [
            'list'  => $list,
            'total' => $total,
        ];
        return self::returnMsg($data);
    }

    //返回失败
    public static function returnError(int $code = 20001, string $msg = '')
    {
        return response()->json([
            'code' => $code,
            'msg'  => $msg,
            'data' => [],
        ]);
    }


}

