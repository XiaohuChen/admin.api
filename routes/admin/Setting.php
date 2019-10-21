<?php

//配置路由

//等级配置列表
$router->get('/setting/invite', [
    'as' => 'SettingInvite', 'uses' => 'SettingController@InviteList',
]);

$router->post('/setting/invite/edit', [
    'as' => 'SettingInviteEdit', 'uses' => 'SettingController@InviteEdit',
]);

$router->get('/setting/world', [
    'as' => 'SettingWorld', 'uses' => 'SettingController@World',
]);

$router->post('/setting/world/edit', [
    'as' => 'SettingWorldEdit', 'uses' => 'SettingController@WorldEdit',
]);

$router->get('/setting/plan', [
    'as' => 'SettingPlan', 'uses' => 'SettingController@Plan',
]);

$router->post('/setting/plan/edit', [
    'as' => 'SettingPlanEdit', 'uses' => 'SettingController@PlanEdit',
]);


