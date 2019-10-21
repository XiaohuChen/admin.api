<?php

//理财

$router->get('/invest/list',[
    'as' => 'InvestList', 'uses' => 'InvestController@List'
]);

$router->post('/invest/pass',[
    'as' => 'InvestPass', 'uses' => 'InvestController@Pass'
]);
