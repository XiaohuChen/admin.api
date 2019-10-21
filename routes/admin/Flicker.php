<?php
/**
 * Created by PhpStorm.
 * User: ChenJulong
 * Date: 2019/9/2
 * Time: 14:31
 */


//闪兑列表
$router->get('/flicker/flickerList', [
    'as' => 'flickerList', 'uses' => 'FlickerController@flickerList'
]);

//闪兑设置
$router->get('/flicker/flickerEdit',[
    'as' => 'flickerEdit', 'uses' => 'FlickerController@flickerEdit'
]);

