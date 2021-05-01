<?php

// Load the composer autoloader
require __DIR__ . '/vendor/autoload.php';

// Create Router instance
$router = new \Bramus\Router\Router();

// Define core, database and headers helper class
include_once 'core/config.php';
include_once 'core/database.php';
include_once 'core/headers.php';
include_once 'core/dateshelper.php';

// Define models & controllers
include_once 'models/shop.php';
include_once 'models/jwt.php';
include_once 'models/user.php';
include_once 'models/usertoken.php';
include_once 'controllers/shop.controller.php';
include_once 'controllers/user.controller.php';

// Define routes
require 'pre_routes.php'; // Comment this out remove auth on API
require 'routes.php';

// Run it!
$router->run();