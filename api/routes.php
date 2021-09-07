<?php

/*
    Router logic supplied by bramus\router (https://github.com/bramus/router)

    Useing some example uses from https://github.com/wdekkers/raspberry-pi-app

    Regex cheat sheet: https://courses.cs.washington.edu/courses/cse154/15sp/cheat-sheets/php-regex-cheat-sheet.pdf


*/

// General config
$router->setNamespace('\Controllers'); // Allows us to omit '\Controllers' from method names

// Custom 404 Handler
$router->set404(function() {
  header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
  http_response_code(404);  
  echo json_encode(
      array("message" => "404, route not found!")
  );
});


/***************/
/* Auth Routes */
/***************/
/*$router->post('/auth', function () {
    require 'authenticate/auth.php'; 
} );*/
$router->mount('/auth', function() use ($router) {
    $router->post('/', function () {include 'authenticate/auth.php'; } );
    $router->get('/refresh', function () {include 'authenticate/refresh.php'; } );
    $router->delete('/revoke', function () {include 'authenticate/revoke.php'; } );
});

/***************/
/* Takings Routes */
/***************/
$router->mount('/takings', function () use ($router) {
    $router->post('/', 'TakingsCtl@create');
    $router->put('/(\d+)', 'TakingsCtl@update');
    $router->delete('/(\d+)', 'TakingsCtl@delete');
    $router->get('/quickbooks/(\d+)', 'TakingsCtl@read_by_quickbooks_status');
    $router->get('/(\d+)', 'TakingsCtl@read_one');
});

/***************/
/* Shop Routes */
/***************/
$router->mount('/shop', function () use ($router) {
    $router->get('/', 'ShopCtl@read_all');
    $router->get('/(\d+)', 'ShopCtl@read_one');
    $router->get('/(\D+)', 'ShopCtl@read_one_name');
});

/*********************/
/* Quickbooks Routes */
/*********************/
$router->mount('/qb', function () use ($router) {
    // The param is the Quickbooks Journal Id. This number is not exposed via the normal QB website. It
    // is not the DocNumber which can be seen on the website.
    $router->get('/journal/(\w+)', 'JournalCtl@read_one');


    $router->post('/journal', 'JournalCtl@create');

     // The param is the takingsid value in the takings table in MySQL dB
    $router->post('/journal/takings/(\d+)', 'JournalCtl@create_from_takings');

    // take action on takings journal; Only 'create_all'implemented so far.
    $router->patch('/journal/takings/', 'JournalCtl@patch');

    $router->get('/auth', 'QuickbooksCtl@oauth2_begin');
    $router->get('/callback', 'QuickbooksCtl@oauth2_callback');
    $router->get('/refresh', 'QuickbooksCtl@oauth2_refresh');
    $router->delete('/', 'QuickbooksCtl@oauth2_revoke');
});

/***************/
/* User Routes */
/***************/
$router->mount('/user', function () use ($router) {

    // will result in '/user'
    $router->get('/', 'UserCtl@read_all');

    // will result in '/user/id'
    $router->get('/(\d+)', 'UserCtl@read_one');

    // new user
    $router->post('/', 'UserCtl@create');

    // delete user
    $router->delete('/(\d+)', 'UserCtl@delete');

    // update user
    $router->put('/(\d+)', 'UserCtl@update');
});


