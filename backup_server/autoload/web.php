<?php

    include('./route.php');


    // Route File
    // Route::method('url/{params}','controller@function');
    // Example:

    
    // Route::middleware('auth', function(){
    Route::get('/','MainController@list_all');
    Route::post('/','MainController@upload');
    Route::post('/check','MainController@check_file');
    Route::get('/download','MainController@download');
    Route::post('/rename','MainController@rename_file');
    Route::post('/delete','MainController@delete');
    
    Route::post('/remote', 'MainController@add_remote');
    
    Route::post('/login', 'MainController@login');
    Route::post('/move', 'MainController@move');
    
    Route::post('/publish/list','MainController@list_publish');
    Route::post('/publish/clear','MainController@clear_publish');
    Route::post('/publish', 'MainController@publish');

    Route::post('/pull','MainController@pull_publish');
    Route::get('/pull/download','MainController@pull_download');
    // });
    // Route::group('/api', function(){
    //     Route::middleware('auth', function(){
    //         Route::get('/', 'MainController@api');
    //     });
    // });
