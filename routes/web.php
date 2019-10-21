<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('index', [
    'as' => 'Index', 'uses' => 'IndexController@Index',
]);

$router->post('/user/login', [
    'as' => 'Login', 'uses' => 'IndexController@Login',
]);


$router->get('/index/statistics', [
    'as' => 'statistics', 'uses' => 'IndexController@Statis',
]);


$router->group(['middleware' => 'VerifyToken'], function () use ($router) {

    $router->get('/qiniu-token', [
        'as' => 'tokenGet', 'uses' => 'QiniuController@tokenGet',
    ]);
    $router->get('/user/info', [
        'as' => 'UserInfo', 'uses' => 'IndexController@userInfo',
    ]);
    $router->get('/user/list', [
        'as' => 'UserList', 'uses' => 'MemberController@List',
    ]);
    $router->post('/user/logout', [
        'as' => 'UserLogout', 'uses' => 'IndexController@Logout',
    ]);
    $router->get('/rule/list', [
        'as' => 'RuleList', 'uses' => 'RuleController@List',
    ]);
    $router->post('/rule/edit', [
        'as' => 'RuleEdit', 'uses' => 'RuleController@Edit',
    ]);
    $router->post('/rule/add', [
        'as' => 'RuleAdd', 'uses' => 'RuleController@Add',
    ]);
    $router->get('/rule/group', [
        'as' => 'RuleGroup', 'uses' => 'RuleController@Group',
    ]);
    $router->post('/rule/del', [
        'as' => 'RuleDel', 'uses' => 'RuleController@Delete',
    ]);
    $router->post('/rule/group/edit', [
        'as' => 'RuleGroupEdit', 'uses' => 'RuleController@EditGroup',
    ]);
    $router->post('/rule/group/add', [
        'as' => 'RuleGroupAdd', 'uses' => 'RuleController@AddGroup',
    ]);
    $router->post('/rule/group/del', [
        'as' => 'RuleGroupDel', 'uses' => 'RuleController@DelGroup',
    ]);

    $router->get('/admin/list', [
        'as' => 'AdminList', 'uses' => 'AdminController@List',
    ]);
    $router->post('/admin/del', [
        'as' => 'AdminDel', 'uses' => 'AdminController@Delete',
    ]);
    $router->post('/admin/addAdmin', [
        'as' => 'addAdmin', 'uses' => 'AdminController@addAdmin',
    ]);
    $router->post('/admin/updateAdmin', [
        'as' => 'updateAdmin', 'uses' => 'AdminController@updateAdmin',
    ]);
    $router->get('/admin/ruleList', [
        'as' => 'ruleList', 'uses' => 'AdminController@ruleList',
    ]);
    $router->get('/admin/getAdmin', [
        'as' => 'getAdmin', 'uses' => 'AdminController@getAdmin',
    ]);
    //更换谷歌验证码秘钥
    $router->get('/admin/adminGuge', [
        'as' => 'adminGuge', 'uses' => 'AdminController@adminGuge',
    ]);
    //后台操作日志
    $router->get('/admin/adminLogList', [
        'as' => 'adminLogList', 'uses' => 'AdminController@adminLogList',
    ]);


    require 'admin/BannerNotice.php';
    require 'admin/Article.php';
    require 'admin/Coin.php';
    require 'admin/Help.php';
    require 'admin/Members.php';
    require 'admin/Config.php';
    require 'admin/Flicker.php';
    require 'admin/Setting.php';
    require 'admin/LockStore.php';
    require 'admin/Trade.php';
    require 'admin/Trade.php';
    require 'admin/Product.php';
    require 'admin/Community.php';
    require 'admin/Plan.php';
    require 'admin/Output.php';
    require 'admin/Invest.php';
});
