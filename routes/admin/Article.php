<?php

//文章分类路由
$router->get('/article/articleCateList',[
    'as' => 'articleCateList', 'uses' => 'ArticleController@articleCateList'
]);

$router->post('/article/updateCate', [
    'as' => 'updateCate', 'uses' => 'ArticleController@updateCate'
]);

$router->post('/article/deleteCate', [
    'as' => 'deleteCate', 'uses' => 'ArticleController@deleteCate'
]);

$router->post('/article/addCate',[
    'as' => 'addCate', 'uses' => 'ArticleController@addCate'
]);

$router->get('/article/getCate', [
    'as' => 'getCate', 'uses' => 'ArticleController@getCate'
]);


//文章路由
$router->get('/article/articleList',[
    'as' => 'articleList', 'uses' => 'ArticleController@articleList'
]);

$router->post('/article/updateArticle', [
    'as' => 'updateArticle', 'uses' => 'ArticleController@updateArticle'
]);


$router->get('/article/deleteArticle', [
    'as' => 'deleteArticle', 'uses' => 'ArticleController@deleteArticle'
]);

$router->post('/article/addArticle',[
    'as' => 'addArticle', 'uses' => 'ArticleController@addArticle'
]);

$router->get('/article/getArticle', [
    'as' => 'getArticle', 'uses' => 'ArticleController@getArticle'
]);
