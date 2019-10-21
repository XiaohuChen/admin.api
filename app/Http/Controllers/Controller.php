<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Libraries\Send;
use App\Libraries\Tools;

class Controller extends BaseController
{
    use Send, Tools;


    /**
     * Notes:清除Redis所有缓存
     */
    public static function redisFlushAll()
    {

        $ip    = env('REDIS_HOST');
        $port  = env('REDIS_PORT');
        $redis = new \Redis();
        $redis->pconnect($ip, $port, 1);
        $redis->flushAll();
    }
}
