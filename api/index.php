<?php

/**
 * This is the entry point for the api. It loads third party scripts from '/vendor/' and
 * then requires all the php code files that make up the api. Finally it starts the 
 * router so that when the user visits api endpoints they are directed to the correct place.
 * 
 * The structure of the app is:
 * 1) Core php scripts that store constants, allow access to the database and provide helper functions
 * 2) Controller classes that provide CRUD operations on the various models.
 * 3) Model classes that retrieve/store data and contian business logic.
 * 4) Routes.php and pre_routes.php which govern app routings.
 * 5) Third party code in /vendor/
 */

// Load the composer autoloader
require __DIR__ . '/vendor/autoload.php';

// Create Router instance
$router = new \Bramus\Router\Router();

// Define core, database and helper classes
require 'core/config.php';
require 'core/database.php';
require 'core/headers.php';
require 'core/dateshelper.php';
require 'core/qboconstants.php';

// Define controllers
require 'controllers/auditlog.controller.php';
require 'controllers/quickbooks/qbauth.controller.php';
require 'controllers/quickbooks/qbbill.controller.php';
require 'controllers/quickbooks/qbclass.controller.php';
require 'controllers/quickbooks/qbcompany.controller.php';
require 'controllers/quickbooks/qbemployee.controller.php';
require 'controllers/quickbooks/qbitem.controller.php';
require 'controllers/quickbooks/qbjournal.controller.php';
require 'controllers/quickbooks/qbpayrolljournal.controller.php';
require 'controllers/quickbooks/qbpayrollquery.controller.php';
require 'controllers/quickbooks/qbrealm.controller.php';
require 'controllers/quickbooks/qbrecurringtxn.controller.php';
require 'controllers/quickbooks/qbreport.controller.php';
require 'controllers/quickbooks/qbsalesreceipt.controller.php';
require 'controllers/report.controller.php';
require 'controllers/shop.controller.php';
require 'controllers/takings.controller.php';
require 'controllers/user.controller.php';
require 'controllers/xlsx.controller.php';

// Define models
require 'models/auditlog.php';
require 'models/encryptedxlsx.php';
require 'models/jwt.php';
require 'models/payrollbase.php'; // This must be included before the files that depend on it.
require 'models/payrollcsv.php'; // Depends on payrollbase.php
require 'models/payrollxlsx.php'; // Depends on payrollbase.php
require 'models/payslip.php';
require 'models/quickbooks/qbauth.php';
require 'models/quickbooks/qbdatemacroenum.php';
require 'models/quickbooks/qbbill.php'; // This must be included before the files that depend on it.
require 'models/quickbooks/qbclass.php';
require 'models/quickbooks/qbemployee.php';
require 'models/quickbooks/qbitem.php';
require 'models/quickbooks/qbjournal.php'; // This must be included before the files that depend on it.
require 'models/quickbooks/qbnijournal.php'; // Depends on qbjournal.php
require 'models/quickbooks/qbpayrolljournal.php'; // Depends on qbjournal.php
require 'models/quickbooks/qbpensionbill.php'; // Depends on qbbill.php
require 'models/quickbooks/qbquery.php';
require 'models/quickbooks/qbsalesreceipt.php';
require 'models/quickbooks/qbshopjournal.php'; // Depends on qbjournal.php
require 'models/quickbooks/qbrealm.php';
require 'models/quickbooks/qbrecurringtxn.php';
require 'models/quickbooks/qbreport.php';
require 'models/quickbooks/qbtoken.php';
require 'models/report.php';
require 'models/rowitem.php';
require 'models/shop.php';
require 'models/summary.php';
require 'models/takings.php';
require 'models/user.php';
require 'models/usertoken.php';

// QB Report models
require 'models/quickbooks/qbreport/qbitemsales.php';
require 'models/quickbooks/qbreport/qbgeneralledger.php';
require 'models/quickbooks/qbreport/qbprofitandloss.php';

// Define routes
require 'pre_routes.php';
require 'routes.php';

// Run it!
$router->run();