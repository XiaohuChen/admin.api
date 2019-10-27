<?php

$router->get('/kline/list',[
    'as' => 'KlineList', 'uses' => 'KlineController@List'
]);

