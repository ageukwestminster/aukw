<?php

/*
    Router logic supplied by bramus\router (https://github.com/bramus/router)

    Using some example uses from https://github.com/wdekkers/raspberry-pi-app

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
$router->mount('/auth', function() use ($router) {
    $router->post('/', function () {include 'authenticate/auth.php'; } );
    $router->get('/refresh', function () {include 'authenticate/refresh.php'; } );
    $router->delete('/', function () {include 'authenticate/revoke.php'; } );

    $router->get('/callback', 'QuickbooksCtl@oauth2_callback');
});

/***************/
/* Takings Routes */
/***************/
$router->mount('/takings', function () use ($router) {
    // new takings entry
    $router->post('/', 'TakingsCtl@create');
    // update takings
    $router->put('/(\d+)', 'TakingsCtl@update');
    // delete takings
    $router->delete('/(\d+)', 'TakingsCtl@delete');
    // return all takings objects defined by quickbooks status
    $router->get('/quickbooks/(\d+)', 'TakingsCtl@read_by_quickbooks_status');
    // return all takings defined by shopid
    $router->get('/shop/(\d+)', 'TakingsCtl@read_by_shop');
    // return a single takings with the given id (primary key)
    $router->get('/(\d+)', 'TakingsCtl@read_one');

    // Return the Takings for the most recent date
    $router->get('/most-recent/(\d+)', 'TakingsCtl@read_most_recent');

    // Show takings data for the last 90 days for a given shop
    // A simplification of the next api method
    $router->get('/summary/shop/(\d+)', 'TakingsCtl@summary');

    // Show takings data for the last 'datapoints' days for a given shop
    $router->get('/saleslist/shop/(\d+)/datapoints/(\d+)', 'TakingsCtl@salesList');

    // Update single property on existing takings object
    // Sample body : { "quickbooks": 0 } ... or ... { "quickbooks": 1 }
    $router->patch('/(\d+)', 'TakingsCtl@patch');
});

/***************/
/* Report Routes */
/***************/
$router->mount('/report', function () use ($router) {
    $router->get('/histogram', 'ReportCtl@dailySalesHistogram');
    $router->get('/moving-avg', 'ReportCtl@dailySalesMovingAverage');
    $router->get('/profitandloss', 'QBReportCtl@profit_and_loss');
    $router->get('/salesbyitem', 'QBReportCtl@sales_by_item');
});

/***************/
/* Shop Routes */
/***************/
$router->mount('/shop', function () use ($router) {
    // return all shops
    $router->get('/', 'ShopCtl@read_all');
    // return one shop, with the given id (primary key)
    $router->get('/(\d+)', 'ShopCtl@read_one');
    // return one shop, with the given name
    $router->get('/(\D+)', 'ShopCtl@read_one_name');
});

/******************/
/* Summary Routes */
/******************/
$router->mount('/summary', function () use ($router) {
    $router->get('/', 'TakingsSummaryCtl@performanceSummary');
    $router->get('/sales-chart', 'TakingsSummaryCtl@salesChart');
    $router->get('/dept-chart', 'TakingsSummaryCtl@departmentChart');
    // Dynamic route with (successive) optional subpatterns: /monthly-sales/shopid(/year(/month(/day)))
    $router->get('/monthly-sales/(\d+)(/\d{4}(/\d{2}(/\d{2})?)?)?', 'TakingsSummaryCtl@salesByMonth');
    $router->get('/quarterly-sales/(\d+)(/\d{4})?', 'TakingsSummaryCtl@salesByQuarter');
});

/*********************/
/* Quickbooks Routes */
/*********************/
$router->mount('/qb', function () use ($router) {
    // The param is the Quickbooks Journal Id. This number is not easily seen on 
    // the normal QB website but it can been seen in Audit Log.
    // It is not the DocNumber which can be seen on the website.
    $router->get('/journal/(\w+)', 'JournalCtl@read_one');                                                      

    // The param is the Quickbooks Journal Id. This number is not easily seen on 
    // the normal QB website but it can been seen in Audit Log.
    // It is not the DocNumber which can be seen on the website.
    $router->get('/salesreceipt/(\w+)', 'SalesReceiptCtl@read_one');

    // Create a new sales receipt in QB
    $router->post('/salesreceipt', 'SalesReceiptCtl@create');

    // The param is the takingsid value in the takings table in MySQL dB
    $router->post('/salesreceipt/takings/(\d+)', 'SalesReceiptCtl@create_from_takings');
    // take action on takings journal; Only 'create_all' implemented so far.
    // Create All adds to QB any takings which has Quickbooks=0 in the mariaDB
    $router->patch('/salesreceipt/takings/', 'SalesReceiptCtl@patch');

    // Returns the uri needed to start the QBO authorisation process
    $router->get('/auth', 'QuickbooksCtl@oauth2_begin');    
    // Exchange a refresh token for a new access toekn
    $router->get('/refresh', 'QuickbooksCtl@oauth2_refresh');
    // Delete QBO authorisation
    $router->delete('/', 'QuickbooksCtl@oauth2_revoke');

    // Retrieve details of the connection to QB (if any)
    $router->get('/connection', 'QBTokenCtl@read_one');

    // QB item is for Products/Services
    $router->get('/item/(\w+)', 'QBItemCtl@read_one');
    $router->get('/items', 'QBItemCtl@read_all');
});

/***************/
/* User Routes */
/***************/
$router->mount('/user', function () use ($router) {

    // return list of all users
    $router->get('/', 'UserCtl@read_all');

    // return single user that has the given id
    $router->get('/(\d+)', 'UserCtl@read_one');

    // new user
    $router->post('/', 'UserCtl@create');

    // delete user
    $router->delete('/(\d+)', 'UserCtl@delete');

    // update user
    $router->put('/(\d+)', 'UserCtl@update');
});



