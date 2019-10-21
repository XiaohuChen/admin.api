<?php
/**
 * Created by PhpStorm.
 * User: ChenJulong
 * Date: 2019/9/6
 * Time: 14:29
 */


//锁仓路由

//锁仓规则配置
$router->get('/lockStore/lockStoreList', [
    'as' => 'lockStoreList', 'uses' => 'LockStoreController@lockStoreList',
]);

//锁仓规则配置
$router->post('/lockStore/lockStoreEdit', [
    'as' => 'lockStoreEdit', 'uses' => 'LockStoreController@lockStoreEdit',
]);

//会员锁仓记录
$router->get('/lockStore/lockStoreMemberList', [
    'as' => 'lockStoreList', 'uses' => 'LockStoreController@lockStoreMemberList',
]);

//会员锁仓奖励列表
$router->get('/lockStore/lockStoreRewaraLog', [
    'as' => 'lockStoreList', 'uses' => 'LockStoreController@lockStoreRewaraLog',
]);

//会员释放仓记录
$router->get('/lockStore/IAFreeLog', [
    'as' => 'IAFreeLog', 'uses' => 'LockStoreController@IAFreeLog',
]);

//产出记录
$router->get('/lockStore/IAOutput', [
    'as' => 'IAOutput', 'uses' => 'LockStoreController@IAOutput',
]);


