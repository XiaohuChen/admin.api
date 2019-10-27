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

$router->post('/bannerNotice/newsAdd', [
    'as' => 'NewsAdd', 'uses' => 'BannerNoticeController@NewsAdd'
]);




//公告路由
$router->get('/bannerNotice/noticeList', [
    'as' => 'NoticeList', 'uses' => 'BannerNoticeController@noticeList'
]);

$router->get('/bannerNotice/qa', [
    'as' => 'QaList', 'uses' => 'BannerNoticeController@QaList'
]);

$router->post('/bannerNotice/noticeUpdate', [
    'as' => 'noticeUpdate', 'uses' => 'BannerNoticeController@noticeUpdate'
]);

$router->post('/bannerNotice/newsUpdate', [
    'as' => 'NewsUpdate', 'uses' => 'BannerNoticeController@NewsUpdate'
]);

$router->post('/bannerNotice/qaUpdate', [
    'as' => 'QaUpdate', 'uses' => 'BannerNoticeController@QaUpdate'
]);


$router->get('/bannerNotice/noticeDelete', [
    'as' => 'noticeDelete', 'uses' => 'BannerNoticeController@noticeDelete'
]);

$router->get('/bannerNotice/qaDelete', [
    'as' => 'qaDelete', 'uses' => 'BannerNoticeController@QaDelete'
]);



$router->get('/bannerNotice/newsDelete', [
    'as' => 'NewsDelete', 'uses' => 'BannerNoticeController@NewsDelete'
]);

$router->get('/bannerNotice/getNotice', [
    'as' => 'NoticeDelete', 'uses' => 'BannerNoticeController@getNotice'
]);

$router->get('/bannerNotice/getQa', [
    'as' => 'GetQA', 'uses' => 'BannerNoticeController@GetQA'
]);


$router->get('/bannerNotice/getNews', [
    'as' => 'GetNews', 'uses' => 'BannerNoticeController@GetNews'
]);



$router->post('/bannerNotice/noticeAdd', [
    'as' => 'NoticeAdd', 'uses' => 'BannerNoticeController@noticeAdd'
]);

$router->post('/bannerNotice/qaAdd', [
    'as' => 'qaAdd', 'uses' => 'BannerNoticeController@QaAdd'
]);

$router->get('/bannerNotice/AboutUs', [
    'as' => 'AboutUs', 'uses' => 'BannerNoticeController@AboutUs'
]);

$router->get('/bannerNotice/AboutUsEdit', [
    'as' => 'AboutUsEdit', 'uses' => 'BannerNoticeController@AboutUsEdit'
]);

$router->get('/bannerNotice/MemberDoc', [
    'as' => 'MemberDoc', 'uses' => 'BannerNoticeController@MemberDoc'
]);

$router->get('/bannerNotice/MemberDocEdit', [
    'as' => 'MemberDocEdit', 'uses' => 'BannerNoticeController@MemberDocEdit'
]);

$router->get('/bannerNotice/News', [
    'as' => 'NewsList', 'uses' => 'BannerNoticeController@NewList'
]);




