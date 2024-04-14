<?php

// Load the composer autoloader
require __DIR__ . '/vendor/autoload.php';

// Create Router instance
$router = new \Bramus\Router\Router();

// Define core, database and helper classes
include_once 'core/config.php';
include_once 'core/database.php';
include_once 'core/headers.php';
include_once 'core/dateshelper.php';
include_once 'core/qboconstants.php';

// Define controllers
include_once 'controllers/qbauth.controller.php';
include_once 'controllers/qbbill.controller.php';
include_once 'controllers/qbclass.controller.php';
include_once 'controllers/qbcompany.controller.php';
include_once 'controllers/qbemployee.controller.php';
include_once 'controllers/qbitem.controller.php';
include_once 'controllers/qbjournal.controller.php';
include_once 'controllers/qbpayrolljournal.controller.php';
include_once 'controllers/qbpayrollquery.controller.php';
include_once 'controllers/qbrealm.controller.php';
include_once 'controllers/qbrecurringtxn.controller.php';
include_once 'controllers/qbreport.controller.php';
include_once 'controllers/qbsalesreceipt.controller.php';
include_once 'controllers/report.controller.php';
include_once 'controllers/shop.controller.php';
include_once 'controllers/takings.controller.php';
include_once 'controllers/user.controller.php';
include_once 'controllers/xlsx.controller.php';

// Define models
include_once 'models/encryptedxlsx.php';
include_once 'models/jwt.php';
include_once 'models/payrollxlsx.php';
include_once 'models/payslip.php';
include_once 'models/qbauth.php';
include_once 'models/qbbill.php';
include_once 'models/qbclass.php';
include_once 'models/qbemployee.php';
include_once 'models/qbitem.php';
include_once 'models/qbjournal.php';
include_once 'models/qbnijournal.php'; // Depends on qbjournal.php
include_once 'models/qbpayrolljournal.php'; // Depends on qbjournal.php
include_once 'models/qbpensionbill.php'; // Depends on qbbill.php
include_once 'models/qbsalesreceipt.php';
include_once 'models/qbshopjournal.php'; // Depends on qbjournal.php
include_once 'models/qbrealm.php';
include_once 'models/qbrecurringtxn.php';
include_once 'models/qbreport.php';
include_once 'models/qbtoken.php';
include_once 'models/report.php';
include_once 'models/shop.php';
include_once 'models/summary.php';
include_once 'models/takings.php';
include_once 'models/user.php';
include_once 'models/usertoken.php';

// Define routes
require 'pre_routes.php';
require 'routes.php';

// Run it!
$router->run();