<?php

//收益记录
$router->get('/output/list',[
    'as' => 'OutputList', 'uses' => 'OutputController@List'
]);