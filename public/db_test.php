<?php

// Define BASE_PATH if not already defined (adjust if necessary based on project structure)
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__)); // Assumes db_test.php is in public/
}

// Include the configuration file
$configPath = BASE_PATH . '/app/config.php';
if (!file_exists($configPath)) {
    die("Error: Configuration file not found at " . htmlspecialchars($configPath));
}
$config = require $configPath;

// Extract database credentials
$host = $config['DB_HOST'] ?? null;
$db_name = $config['DB_NAME'] ?? null;
$username = $config['DB_USER'] ?? null;
$password = $config['DB_PASS'] ?? null;
$charset = 'utf8'; // Or get from config if defined there

// Check if credentials are set
if (!$host || !$db_name || !$username) { // Password can be empty sometimes
    die("Error: Database credentials missing in configuration.");
}

// Construct DSN
$dsn = "mysql:host=" . $host . ";dbname=" . $db_name . ";charset=" . $charset;
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

echo "Attempting database connection...<br>";
echo "Host: " . htmlspecialchars($host) . "<br>";
echo "Database: " . htmlspecialchars($db_name) . "<br>";
echo "User: " . htmlspecialchars($username) . "<br>";
echo "DSN: " . htmlspecialchars($dsn) . "<br>";

try {
    // Attempt connection
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "<br><strong>Connection Success!</strong>";
    $pdo = null; // Close connection
} catch (PDOException $e) {
    // Display error message
    echo "<br><strong>Connection Failed:</strong> " . htmlspecialchars($e->getMessage());
    // Check specifically for driver not found error
    if (strpos($e->getMessage(), 'driver') !== false || strpos($e->getMessage(), 'DataSource') !== false) {
        echo "<br><em>Hint: This often indicates the 'pdo_mysql' PHP extension is missing or not enabled on the server.</em>";
    }
} catch (Throwable $t) {
    // Catch any other errors (like config file issues)
    echo "<br><strong>An unexpected error occurred:</strong> " . htmlspecialchars($t->getMessage());
}

?>