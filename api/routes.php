<?php

/*
    Router logic supplied by bramus\router (https://github.com/bramus/router)

    Useing some example uses from https://github.com/wdekkers/raspberry-pi-app

    Regex cheat sheet: https://courses.cs.washington.edu/courses/cse154/15sp/cheat-sheets/php-regex-cheat-sheet.pdf

    I'm using three different ways of handinling routes:
        1. Pure funciton call. See _>before in 'pre_routes.php'
        2. Whole file like the post route for auth
        3. Method call like read_all for Bank Account

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


