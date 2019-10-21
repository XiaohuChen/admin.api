<?php

//等级分类路由
$router->get('/plan/list',[
    'as' => 'PlantList', 'uses' => 'PlanController@List'
]);

$router->get('/plan/detail',[
    'as' => 'PlantDetail', 'uses' => 'PlanController@Detail'
]);

$router->post('/plan/edit',[
    'as' => 'PlantEdit', 'uses' => 'PlanController@Edit'
]);

$router->post('/plan/add',[
    'as' => 'PlantAdd', 'uses' => 'PlanController@Add'
]);