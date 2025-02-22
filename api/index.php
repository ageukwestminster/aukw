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
require 'controllers/qbauth.controller.php';
require 'controllers/qbbill.controller.php';
require 'controllers/qbclass.controller.php';
require 'controllers/qbcompany.controller.php';
require 'controllers/qbemployee.controller.php';
require 'controllers/qbitem.controller.php';
require 'controllers/qbjournal.controller.php';
require 'controllers/qbpayrolljournal.controller.php';
require 'controllers/qbpayrollquery.controller.php';
require 'controllers/qbrealm.controller.php';
require 'controllers/qbrecurringtxn.controller.php';
require 'controllers/qbreport.controller.php';
require 'controllers/qbsalesreceipt.controller.php';
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
require 'models/qbauth.php';
require 'models/qbdatemacroenum.php';
require 'models/qbbill.php'; // This must be included before the files that depend on it.
require 'models/qbclass.php';
require 'models/qbemployee.php';
require 'models/qbitem.php';
require 'models/qbjournal.php'; // This must be included before the files that depend on it.
require 'models/qbnijournal.php'; // Depends on qbjournal.php
require 'models/qbpayrolljournal.php'; // Depends on qbjournal.php
require 'models/qbpensionbill.php'; // Depends on qbbill.php
require 'models/qbquery.php';
require 'models/qbsalesreceipt.php';
require 'models/qbshopjournal.php'; // Depends on qbjournal.php
require 'models/qbrealm.php';
require 'models/qbrecurringtxn.php';
require 'models/qbreport.php';
require 'models/qbtoken.php';
require 'models/report.php';
require 'models/rowitem.php';
require 'models/shop.php';
require 'models/summary.php';
require 'models/takings.php';
require 'models/user.php';
require 'models/usertoken.php';

// QB Report models
require 'models/qbreport/qbitemsales.php';
require 'models/qbreport/qbgeneralledger.php';
require 'models/qbreport/qbprofitandloss.php';

// Define routes
require 'pre_routes.php';
require 'routes.php';

// Run it!
$router->run();