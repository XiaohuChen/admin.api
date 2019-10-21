<?php

//文章分类路由
$router->get('/coin/coinlist',[
    'as' => 'coinList', 'uses' => 'CoinController@coinList'
]);
