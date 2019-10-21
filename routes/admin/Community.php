<?php

//社区等级路由
$router->get('/community/list',[
    'as' => 'CommunityList', 'uses' => 'CommunityController@List'
]);

$router->get('/community/get',[
    'as' => 'CommunityGet', 'uses' => 'CommunityController@Detail'
]);

$router->get('/community/edit',[
    'as' => 'CommunityEdit', 'uses' => 'CommunityController@Edit'
]);

$router->get('/community/add',[
    'as' => 'CommunityAdd', 'uses' => 'CommunityController@Add'
]);
