<?php

//产品分类路由
$router->get('/product/list',[
    'as' => 'ProductList', 'uses' => 'ProductController@List'
]);

$router->get('/product/get',[
    'as' => 'ProductGet', 'uses' => 'ProductController@Detail'
]);

$router->post('/product/edit',[
    'as' => 'ProductEdit', 'uses' => 'ProductController@Edit'
]);

$router->post('/product/add',[
    'as' => 'ProductAdd', 'uses' => 'ProductController@Add'
]);

