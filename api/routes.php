<?php

/**
 * 
 * Define endpoints and associated routes for the api
 * 
 * Router logic supplied by {@link https://github.com/bramus/router bramus\router}.
 * 
 * Using some example code from {@link https://github.com/wdekkers/raspberry-pi-app github}.
 * 
 * Regex cheat sheet: {@link https://courses.cs.washington.edu/courses/cse154/15sp/cheat-sheets/php-regex-cheat-sheet.pdf PDF File}.
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
    // Login, JWT tokens returned + cookie
    $router->post('/', function () {include 'authenticate/auth.php'; } );
    // Generate a new access token from refresh token
    $router->get('/refresh', function () {include 'authenticate/refresh.php'; } );
    // Logout
    $router->delete('/', function () {include 'authenticate/revoke.php'; } );
    // Returns the uri needed to start the QBO authorisation process
    $router->get('/qb/auth', 'QBAuthCtl@oauth2_begin');
    // QBO callback endpoint for QBO authentication process
    $router->get('/qb/callback', 'QBAuthCtl@oauth2_callback');
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
    $router->get('/summarytable', 'ReportCtl@performanceSummary');
    $router->get('/sales-chart', 'ReportCtl@salesChart');
    $router->get('/dept-chart', 'ReportCtl@departmentChart');
    $router->get('/avg-weekly-sales/(\d+)', 'ReportCtl@avgWeeklySales');
    $router->get('/avg-weekly-sales-by-quarter/(\d+)', 'ReportCtl@avgWeeklySalesByQuarter');
    $router->get('/avg-daily-transaction-size/(\d+)', 'ReportCtl@avgDailyTransactionSize');
    $router->get('/avg-daily-txn-by-quarter/(\d+)', 'ReportCtl@avgDailyTransactionSizeByQuarter');
    $router->get('/sales-by-department/(\d+)', 'ReportCtl@salesByDepartment');
    $router->get('/cash-ratio-moving-avg/(\d+)', 'ReportCtl@cashRatioMovingAverage');
    $router->get('/customer-insights/(\d+)', 'ReportCtl@salesByDepartmentAndCustomerMovingAverage');

    // Dynamic route with (successive) optional subpatterns: /monthly-sales/shopid(/year(/month(/day)))
    $router->get('/monthly-sales/(\d+)(/\d{4}(/\d{2}(/\d{2})?)?)?', 'ReportCtl@salesByMonth');
    $router->get('/quarterly-sales/(\d+)(/\d{4})?', 'ReportCtl@salesByQuarter');

    // Show takings data for the last 90 days for a given shop
    // (A simplification of the following api method)
    $router->get('/takingssummary/shop/(\d+)', 'ReportCtl@takingsSummary');
    // Show takings data for the last 'datapoints' days for a given shop
    $router->get('/saleslist/shop/(\d+)/datapoints/(\d+)', 'ReporCtl@salesList');

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

/*********************/
/* Quickbooks Routes */
/*********************/
$router->mount('/qb', function () use ($router) {
    // The first parameter is realmid, as is customary with the QBO api
    // The second param is the Quickbooks Journal Id. This number is not easily 
    // seen on the normal QB website but it can been seen in Audit Log.
    // It is not the DocNumber which can be seen when adding/editing on QBO.
    $router->get('/(\d+)/journal/(\d+)', 'QBJournalCtl@read_one');
    // Get a list of journals whose DocNumber starts with the given string                                    
    $router->get('/(\d+)/journal/docnumber/(\w+)', 'QBJournalCtl@query_by_docnumber');
    // QB Payroll Journal for individual employee
    $router->post('/(\d+)/journal/employee', 'QBPayrollJournalCtl@create_employee_payslip_journal');
    // QB Payroll Journal for Employer NI 
    $router->post('/(\d+)/journal/employerni', 'QBPayrollJournalCtl@create_employer_ni_journal');
    // QB Payroll Journal for shop (aka 'Enterprises')
    $router->post('/(\d+)/journal/enterprises', 'QBPayrollJournalCtl@create_enterprises_journal');

    // The 2nd param is the Quickbooks Journal Id. This number is not easily seen on 
    // the normal QB website but it can been seen in Audit Log.
    // It is not the DocNumber which can be seen when adding/editing on QBO.
    $router->get('/(\d+)/salesreceipt/(\w+)', 'QBSalesReceiptCtl@read_one');

    // Create a new sales receipt in QB
    $router->post('/(\d+)/salesreceipt', 'QBSalesReceiptCtl@create');
    // Delete a sales receipt in QB
    $router->delete('/(\d+)/salesreceipt/(\w+)', 'QBSalesReceiptCtl@delete');

    // The param is the takingsid value in the takings table in MySQL dB
    $router->post('/(\d+)/salesreceipt/takings/(\d+)', 'QBSalesReceiptCtl@create_from_takings');
    // take action on takings journal; Only 'create_all' implemented so far.
    // Create All adds to QB any takings which has Quickbooks=0 in the mariaDB
    $router->patch('/(\d+)/salesreceipt/takings/', 'QBSalesReceiptCtl@create_all_from_takings');

    // Returns the uri needed to start the QBO authorisation process
    $router->get('/auth', 'QBAuthCtl@oauth2_begin');    
    // Exchange a refresh token for a new access token
    $router->get('/(\d+)/refresh/(\d+)', 'QBAuthCtl@oauth2_refresh');
    // Delete QBO authorisation
    $router->delete('/(\d+)/connection', 'QBAuthCtl@oauth2_revoke');
    // Retrieve details of one of the connections to QB (if any)
    $router->get('/(\d+)/connection', 'QBAuthCtl@connection_details');
    // Retrieve details of the connections to QB (if any)
    $router->get('/connections', 'QBAuthCtl@all_connection_details');
    
    // Retrieve details of the QBO company
    $router->get('/(\d+)/companyinfo', 'QBCompanyCtl@companyInfo');
    // return list of all QB realms in database
    $router->get('/realm', 'QBRealmCtl@read_all');
    // return single user that has the given realm id
    $router->get('/realm/(\w+)', 'QBRealmCtl@read_one');

    // QB item is for Products/Services
    $router->get('/(\d+)/item/(\w+)', 'QBItemCtl@read_one');
    $router->get('/(\d+)/items', 'QBItemCtl@read_all');

    // Return details of a QB bill (aka invoice)
    $router->get('/(\d+)/bill/(\w+)', 'QBBillCtl@read_one');
    // QB Bill for Pension payments
    $router->post('/(\d+)/bill/pensions', 'QBBillCtl@create_pensions_bill');
    // Get a list of bills whose DocNumber starts with the given string                                    
    $router->get('/(\d+)/bill/docnumber/(\w+)', 'QBBillCtl@query_by_docnumber');
    // Delete a bill in QB
    $router->delete('/(\d+)/bill/(\w+)', 'QBBillCtl@delete');

    // QB Class
    $router->get('/(\d+)/class/(\w+)', 'QBClassCtl@read_one');
    $router->get('/(\d+)/classes', 'QBClassCtl@read_all');
    
    // QB Employee
    $router->get('/(\d+)/employee/(\d+)', 'QBEmployeeCtl@read_one');
    $router->get('/(\d+)/employee', 'QBEmployeeCtl@read_all');
    $router->get('/(\d+)/employee/allocations', 'QBPayrollJournalCtl@read_employee_allocations');

    // QB Recurring Transactions
    $router->get('/(\d+)/recurringtransaction/(\w+)', 'QBRecurringTransactionCtl@read_one');
    $router->get('/(\d+)/recurringtransactions', 'QBRecurringTransactionCtl@read_all');

    // QB Payroll Query
    $router->get('/(\d+)/query/payroll/(\d{4})/(\d{2})', 'QBPayrollQueryCtl@query');

    // QB Report
    $router->get('/(\d+)/report/generalledger', 'QBReportCtl@general_ledger');
    $router->get('/(\d+)/report/profitandlossraw', 'QBReportCtl@profit_and_loss_raw');
    $router->get('/(\d+)/report/profitandloss', 'QBReportCtl@profit_and_loss');
    $router->get('/(\d+)/report/salesbyitem', 'QBReportCtl@sales_by_item');
    $router->get('/(\d+)/report/salesbyitemraw', 'QBReportCtl@sales_by_item_raw');
    $router->get('/(\d+)/report/qma', 'QBReportCtl@quarterly_market_report');
    $router->get('/(\d+)/report/ragging-by-quarter', 'QBReportCtl@ragging_by_quarter');

    //QB Attachments
    $router->get('/(\d+)/attachments', 'QBAttachmentCtl@read_by_entity');
    $router->get('/(\d+)/attachment/(\w+)', 'QBAttachmentCtl@read_by_id');
    $router->get('/(\d+)/download-attachments', 'QBAttachmentCtl@download');
    $router->post('/(\d+)/attachments', 'QBAttachmentCtl@create');
});

/***************/
/* User Routes */
/***************/
$router->mount('/user', function () use ($router) {

    // return list of all users
    $router->get('/', 'UserCtl@read_all');

    // return single user that has the given id
    $router->get('/(\d+)', 'UserCtl@read_one');

    // return single user that has the given name an email address
    $router->get('/search', 'UserCtl@read_one_by_name_and_email');

    // new user
    $router->post('/', 'UserCtl@create');

    // delete user
    $router->delete('/(\d+)', 'UserCtl@delete');

    // update user
    $router->put('/(\d+)', 'UserCtl@update');
});

/***************/
/* Xlxs Routes */
/***************/
$router->mount('/xlsx', function () use ($router) {

    // Upload spreadsheet
    $router->post('/upload', 'XlsxCtl@upload');

    // Decrypt file
    $router->post('/decrypt', 'XlsxCtl@decrypt');

    // Parse file
    $router->get('/parse', 'XlsxCtl@parse');

    // Determine Worksheets of interest
    $router->get('/listws', 'XlsxCtl@parse_worksheets');
});

/***************/
/* Audit Log Routes */
/***************/
$router->mount('/auditlog', function () use ($router) {
    // new takings entry
    $router->post('/', 'AuditLogCtl@create');
    // return all audit log records
    $router->get('/', 'AuditLogCtl@read');
});

