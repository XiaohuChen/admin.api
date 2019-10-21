<?php
//banner
$router->get('/bannerNotice/bannerList', [
    'as' => 'bannerList', 'uses' => 'BannerNoticeController@bannerList'
]);

$router->post('/bannerNotice/bannerUpdate', [
    'as' => 'bannerUpdate', 'uses' => 'BannerNoticeController@bannerUpdate'
]);

$router->get('/bannerNotice/bannerDelete', [
    'as' => 'bannerDelete', 'uses' => 'BannerNoticeController@bannerDelete'
]);


$router->get('/bannerNotice/getBanner', [
    'as' => 'getBanner', 'uses' => 'BannerNoticeController@getBanner'
]);

$router->post('/bannerNotice/bannerAdd', [
    'as' => 'bannerAdd', 'uses' => 'BannerNoticeController@bannerAdd'
]);


//公告路由
$router->get('/bannerNotice/noticeList', [
    'as' => 'NoticeList', 'uses' => 'BannerNoticeController@noticeList'
]);

$router->post('/bannerNotice/noticeUpdate', [
    'as' => 'noticeUpdate', 'uses' => 'BannerNoticeController@noticeUpdate'
]);

$router->get('/bannerNotice/noticeDelete', [
    'as' => 'noticeDelete', 'uses' => 'BannerNoticeController@noticeDelete'
]);


$router->get('/bannerNotice/getNotice', [
    'as' => 'NoticeDelete', 'uses' => 'BannerNoticeController@getNotice'
]);

$router->post('/bannerNotice/noticeAdd', [
    'as' => 'NoticeAdd', 'uses' => 'BannerNoticeController@noticeAdd'
]);
