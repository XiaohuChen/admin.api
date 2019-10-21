<?php
//七牛配置
$router->get('/config/list',[
    'as' => 'list', 'uses' => 'ConfigController@list'
]);
$router->post('/config/updateAddQiniu',[
    'as' => 'updateAddQiniu','uses' => 'ConfigController@updateAddQiniu'
]);
//短信配置
$router->get('/config/smsList',[
    'as' => 'smsList', 'uses' => 'ConfigController@smsList'
]);
$router->post('/config/updateAddSms',[
    'as' => 'updateAddSms','uses' => 'ConfigController@updateAddSms'
]);
$router->get('/config/smstype',[
    'as' => 'smstype', 'uses' => 'ConfigController@smstype'
]);
//版本配置
$router->get('/config/appList',[
    'as' => 'appList','uses' => 'ConfigController@appList'
]);
$router->post('/config/updateAddAppVersion',[
    'as' => 'updateAddAppVersion','uses' => 'ConfigController@updateAddAppVersion'
]);

//SacConfig 系统配置列表
$router->get('/config/configList',[
    'as' => 'configList','uses' => 'ConfigController@configList'
]);

//SacConfig 系统配置更新
$router->post('/config/configEdit',[
    'as' => 'configEdit','uses' => 'ConfigController@configEdit'
]);

//系统配置
$router->get('/config/settingList',[
    'as' => 'settingList','uses' => 'ConfigController@settingList'
]);


//SacConfig 系统配置更新
$router->post('/config/settingEdit',[
    'as' => 'settingEdit','uses' => 'ConfigController@settingEdit'
]);
