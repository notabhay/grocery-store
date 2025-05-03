<?php


try {
    $logFilePath = dirname(__DIR__) . '/logs/app.log'; 
    $logMessage = sprintf(
        "[%s] Request Received: URI=[%s], SCRIPT_NAME=[%s], PHP_SELF=[%s], Calculated BASE_URL attempt starts...\n",
        date('Y-m-d H:i:s'),
        $_SERVER['REQUEST_URI'] ?? 'N/A',
        $_SERVER['SCRIPT_NAME'] ?? 'N/A',
        $_SERVER['PHP_SELF'] ?? 'N/A'
    );
    
    error_log($logMessage, 3, $logFilePath);
} catch (\Throwable $e) {
    
    error_log("Failed to write diagnostic log: " . $e->getMessage() . "\n", 3, $logFilePath);
}






define('BASE_PATH', dirname(__DIR__));



$scheme = 'http'; 
if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1)) {
    
    $scheme = 'https';
} elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    
    $scheme = 'https';
} elseif (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') {
    
    $scheme = 'https';
} elseif (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
    
    $scheme = 'https';
}


$host = $_SERVER['HTTP_HOST']; 


$script_dir = dirname($_SERVER['SCRIPT_NAME']);

$base_path_url = rtrim(str_replace('\\', '/', $script_dir), '/') . '/';

if ($base_path_url === '
    $base_path_url = '/';
}


define('BASE_URL', $scheme . '://' . $host . $base_path_url);




require_once BASE_PATH . '/vendor/autoload.php';


$required_extensions = ['pdo_mysql', 'session', 'gd', 'mbstring', 'json']; 
$missing_extensions = [];
foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}
if (!empty($missing_extensions)) {
    http_response_code(500); 
    die('ERROR: Required PHP extension(s) missing: ' . implode(', ', $missing_extensions) . '. Please contact server administrator.');
}




$config = require_once BASE_PATH . '/app/config.php';



use App\Core\Request;       
use App\Core\Router;        
use App\Core\Database;      
use App\Core\Registry;      
use App\Core\Session;       
use App\Helpers\CaptchaHelper; 
use Monolog\Logger;         
use Monolog\Handler\StreamHandler; 
use App\Helpers\SecurityHelper; 





SecurityHelper::setSecurityHeaders();



Registry::bind('config', $config);




try {
    
    
    $logFilePath = BASE_PATH . '/logs/app.log';
    
    $logDir = dirname($logFilePath);
    
    if (!is_dir($logDir)) {
        
        mkdir($logDir, 0775, true);
    }
    
    $logger = new Logger('app');
    
    
    $logLevel = ($config['DEBUG_MODE'] ?? false) ? Logger::DEBUG : Logger::WARNING;
    
    $logger->pushHandler(new StreamHandler($logFilePath, $logLevel));
    
    Registry::bind('logger', $logger);

    
    
    $db = new Database(
        $config['DB_HOST'],
        $config['DB_NAME'],
        $config['DB_USER'],
        $config['DB_PASS']
    );
    
    if ($db->getConnection()) {
        
        Registry::bind('database', $db);
    } else {
        
        $errorMessage = "Database Connection Error: Sorry, we couldn't connect to the database. Please try again later.";
        
        if (Registry::has('logger')) {
            Registry::get('logger')->critical("Database connection failed.", ['error' => $db->getError()]);
        } else {
            
            error_log("CRITICAL: Database connection failed. Error: " . $db->getError());
        }
        
        http_response_code(503);
        
        echo "<h1>Database Connection Error</h1>";
        echo "<p>Sorry, we couldn't connect to the database. Please try again later.</p>";
        
        if ($config['DEBUG_MODE'] ?? false) {
            echo "<p>Error details: " . htmlspecialchars($db->getError()) . "</p>";
        }
        
        exit;
    }

    
    
    $sessionConfig = [
        
        'session_timeout' => $config['AUTH_TIMEOUT'] ?? 1800,
    ];
    
    $session = new Session($sessionConfig);
    
    Registry::bind('session', $session);

    
    
    $captchaHelper = new CaptchaHelper($session);
    
    Registry::bind('captchaHelper', $captchaHelper);

    
    
    $request = new Request();
    
    Registry::bind('request', $request);
} catch (\Exception $e) {
    
    
    $errorMessage = "Bootstrap Error (DI Setup): " . $e->getMessage();
    
    if (Registry::has('logger')) {
        Registry::get('logger')->critical($errorMessage, ['exception' => $e]);
    } else {
        
        error_log("CRITICAL: " . $errorMessage);
    }
    
    http_response_code(500);
    
    echo "<h1>Application Initialization Error</h1>";
    echo "<p>An error occurred during application setup. Please try again later.</p>";
    
    if ($config['DEBUG_MODE'] ?? false) {
        echo "<p>Error details: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    exit;
}





if (!$session->start()) {
    
    Registry::get('logger')->critical("Failed to start session.");
    
    http_response_code(500);
    
    echo "<h1>Session Error</h1><p>Could not start session. Please check server configuration.</p>";
    
    exit;
}



$session->validateActivity();



try {
    
    
    
    Router::load(BASE_PATH . '/app/routes.php')
        ->direct($request->uri(), $request->method());
} catch (\Exception $e) {
    
    
    $logger = Registry::get('logger'); 
    
    $logger->error("Unhandled Exception: " . $e->getMessage(), ['exception' => $e]);

    
    
    $statusCode = $e->getCode();
    
    if (!is_int($statusCode) || $statusCode < 100 || $statusCode > 599) {
        $statusCode = 500;
    }
    http_response_code($statusCode); 

    
    if ($statusCode == 404) {
        
        
        $requestUriPathForCheck = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
        $baseUrlPathForCheck = parse_url(BASE_URL, PHP_URL_PATH);
        $requestPathCleanForCheck = trim($requestUriPathForCheck, '/');
        $basePathCleanForCheck = trim($baseUrlPathForCheck, '/');
        $appPathForCheck = '/';
        if (strpos($requestPathCleanForCheck, $basePathCleanForCheck) === 0) {
            $appPathSegmentForCheck = substr($requestPathCleanForCheck, strlen($basePathCleanForCheck));
            $appPathForCheck = '/' . ltrim($appPathSegmentForCheck, '/');
        } elseif (empty($basePathCleanForCheck) && !empty($requestPathCleanForCheck)) {
            $appPathForCheck = '/' . $requestPathCleanForCheck;
        }
        if (empty(trim($appPathForCheck, '/'))) {
            $appPathForCheck = '/';
        }

        
        if (strpos($appPathForCheck, '/api/') === 0) {
            
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Not Found', 'message' => $e->getMessage()]);
            exit; 
        }
        
    }

    
    if ($config['DEBUG_MODE'] ?? false) {
        
        echo '<h1>Error (' . $statusCode . ')</h1>';
        echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        
        $displayMessage = '';
        
        if (Registry::has('session')) {
            $session = Registry::get('session');
            if ($session->hasFlash('error')) {
                
                $displayMessage = $session->getFlash('error');
            }
        }

        
        if ($statusCode == 404) {
            
            echo '<h1>404 - Page Not Found</h1>';
            echo '<p>Sorry, the page you are looking for could not be found.</p>';
        } else {
            echo '<h1>An Error Occurred</h1>';
            echo '<p>We are sorry, something went wrong. Please try again later.</p>';
            
            if (!empty($displayMessage)) {
                echo '<p>Details: ' . htmlspecialchars($displayMessage) . '</p>';
            }
        }
    }
    
}