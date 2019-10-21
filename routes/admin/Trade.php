<?php
/**
 * Created by PhpStorm.
 * User: ChenJulong
 * Date: 2019/9/16
 * Time: 10:38
 */

//配置路由

//等级配置列表
$router->get('/Trade/IATradeList', [
    'as' => 'IATradeList', 'uses' => 'TradeController@IATradeList',
]);
