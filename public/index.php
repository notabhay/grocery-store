<?php

// Define the base path
define('BASE_PATH', dirname(__DIR__));

// Attempt to include the Composer autoloader
require_once BASE_PATH . '/vendor/autoload.php';

// If the script reaches this point, the autoloader include did not cause a fatal error.
die('DEBUG: Autoloader included successfully!');
