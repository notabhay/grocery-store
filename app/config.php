<?php
date_default_timezone_set('Europe/London');
$debugMode = false;
$errorLevel = $debugMode ? E_ALL : 0;
$displayErrors = $debugMode ? 1 : 0;
error_reporting($errorLevel);
ini_set('display_errors', $displayErrors);
return [
    'SITE_NAME' => 'GhibliGroceries', 
    'SITE_URL' => 'https://teach.scam.keele.ac.uk/prin/y1d13/advanced-web-technologies/grocery-store/', 
    'ADMIN_EMAIL' => 'admin@ghibligroceries.com', 
    'DB_HOST' => 'localhost', 
    'DB_NAME' => 'y1d13', 
    'DB_USER' => 'y1d13', 
    'DB_PASS' => 'y1d13y1d13', 
    'AUTH_TIMEOUT' => 3600, 
    'MAX_LOGIN_ATTEMPTS' => 5, 
    'LOCKOUT_TIME' => 900, 
    'CSRF_EXPIRY' => 3600, 
    'PASSWORD_COST' => 12, 
    'API_TOKEN_EXPIRY' => 86400, 
    'API_RATE_LIMIT' => 100, 
    'API_VERSION' => '1.0', 
    'API_BASE_PATH' => '/api', 
    'ITEMS_PER_PAGE' => 10, 
    'MAX_FILE_SIZE' => 5 * 1024 * 1024, 
    'ALLOWED_EXTENSIONS' => ['jpg', 'jpeg', 'png', 'gif'], 
    'UPLOAD_DIR' => __DIR__ . '/../public/assets/uploads/products/', 
    'DEBUG_MODE' => $debugMode, 
];
