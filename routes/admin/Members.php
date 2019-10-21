<?php

//会员路由

//会员列表
$router->get('/members/list', [
    'as' => 'membersList', 'uses' => 'MembersController@membersList',
]);

//我的下级会员
$router->get('/members/subList', [
    'as' => 'subList', 'uses' => 'MembersController@subList',
]);

//查看我的持币
$router->get('/members/holdCoin', [
    'as' => 'holdCoin', 'uses' => 'MembersController@holdCoin',
]);

//修改币种余额
$router->post('/members/memberCoinUpdate', [
    'as' => 'memberCoinUpdate', 'uses' => 'MembersController@memberCoinUpdate',
]);

//修改锁定余额
$router->post('/members/memberCoinLockMoney', [
    'as' => 'memberCoinLockMoney', 'uses' => 'MembersController@memberCoinLockMoney',
]);

//禁用启用用户账号
$router->get('/members/membersStatus', [
    'as' => 'membersStatus', 'uses' => 'MembersController@membersStatus',
]);

//根据id获取我的持币
$router->get('/members/getCoinId', [
    'as' => 'getCoinId', 'uses' => 'MembersController@getCoinId',
]);

//获取资金流水记录
$router->get('/members/capitalMovements', [
    'as' => 'capitalMovements', 'uses' => 'MembersController@capitalMovements',
]);

//获取用户收货地址
$router->get('/members/memberAddressList', [
    'as' => 'memberAddressList', 'uses' => 'MembersController@memberAddressList',
]);

//设置会员的VIP状态
$router->get('/members/memberVip', [
    'as' => 'memberVip', 'uses' => 'MembersController@memberVip',
]);

//为memberCoin表增加一个币种信息
$router->get('/members/addCoin', [
    'as' => 'addCoin', 'uses' => 'MembersController@addCoin',
]);


//修改用户交易备注码
$router->post('/members/memberRemark', [
    'as' => 'memberRemark', 'uses' => 'MembersController@memberRemark',
]);
