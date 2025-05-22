<?php
/**
 * Database Connection Diagnostic Script
 * 
 * This script helps diagnose database connection issues by:
 * 1. Displaying all environment variables
 * 2. Testing direct database connections
 * 3. Showing detailed PDO connection information
 */

// Basic PHP configuration
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Function to output information in a readable format
function output($title, $content, $isError = false) {
    echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid " . ($isError ? "red" : "#ddd") . "; border-radius: 5px;'>";
    echo "<h3 style='margin-top: 0; color: " . ($isError ? "red" : "black") . ";'>{$title}</h3>";
    
    if (is_array($content) || is_object($content)) {
        echo "<pre>" . print_r($content, true) . "</pre>";
    } else {
        echo "<p>{$content}</p>";
    }
    
    echo "</div>";
}

// Header
echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Connection Diagnostics</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .sensitive { color: #999; }
    </style>
</head>
<body>
    <div class='container'>
    <h1>Database Connection Diagnostics</h1>";

// 1. Display environment variables (masking sensitive values)
$env = $_ENV;
$server = $_SERVER;

// Mask sensitive values
$sensitiveKeys = ['DB_PASSWORD', 'USERS_DB_PASSWORD', 'MANGA_DB_PASSWORD', 'APP_KEY', 'PASSWORD'];
foreach ($sensitiveKeys as $key) {
    foreach ($env as $envKey => $envValue) {
        if (stripos($envKey, $key) !== false) {
            $env[$envKey] = '[MASKED]';
        }
    }
    foreach ($server as $serverKey => $serverValue) {
        if (stripos($serverKey, $key) !== false) {
            $server[$serverKey] = '[MASKED]';
        }
    }
}

output("Environment Variables", $env);
output("Server Variables", $server);

// 2. Display Laravel environment variables
try {
    // Bootstrap Laravel to access env() function
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    $laravelEnv = [
        'APP_ENV' => env('APP_ENV'),
        'APP_DEBUG' => env('APP_DEBUG'),
        'DB_CONNECTION' => env('DB_CONNECTION'),
        'USERS_DB_HOST' => env('USERS_DB_HOST'),
        'USERS_DB_PORT' => env('USERS_DB_PORT'),
        'USERS_DB_DATABASE' => env('USERS_DB_DATABASE'),
        'USERS_DB_USERNAME' => env('USERS_DB_USERNAME'),
        'USERS_DB_PASSWORD' => '[MASKED]',
        'MANGA_DB_HOST' => env('MANGA_DB_HOST'),
        'MANGA_DB_PORT' => env('MANGA_DB_PORT'),
        'MANGA_DB_DATABASE' => env('MANGA_DB_DATABASE'),
        'MANGA_DB_USERNAME' => env('MANGA_DB_USERNAME'),
        'MANGA_DB_PASSWORD' => '[MASKED]',
        'SESSION_DRIVER' => env('SESSION_DRIVER'),
        'SESSION_CONNECTION' => env('SESSION_CONNECTION'),
    ];
    
    output("Laravel Environment Variables", $laravelEnv);
    
    // 3. Test direct database connections
    try {
        // Get database configuration
        $usersDbConfig = config('database.connections.users_db');
        $mangaDbConfig = config('database.connections.manga_db');
        
        // Display database configurations
        $usersDbConfigSafe = $usersDbConfig;
        $usersDbConfigSafe['password'] = '[MASKED]';
        output("Users Database Configuration", $usersDbConfigSafe);
        
        $mangaDbConfigSafe = $mangaDbConfig;
        $mangaDbConfigSafe['password'] = '[MASKED]';
        output("Manga Database Configuration", $mangaDbConfigSafe);
        
        // Test Users DB connection
        try {
            $usersPdo = new PDO(
                "pgsql:host={$usersDbConfig['host']};port={$usersDbConfig['port']};dbname={$usersDbConfig['database']}",
                $usersDbConfig['username'],
                $usersDbConfig['password']
            );
            $usersPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $usersPdo->query("SELECT 1 as test");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            output("Users Database Connection Test", "SUCCESS: Successfully connected to the users database");
        } catch (PDOException $e) {
            output("Users Database Connection Test", "ERROR: " . $e->getMessage(), true);
            
            // Try connecting without database name
            try {
                $usersPdo = new PDO(
                    "pgsql:host={$usersDbConfig['host']};port={$usersDbConfig['port']}",
                    $usersDbConfig['username'],
                    $usersDbConfig['password']
                );
                $usersPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                $stmt = $usersPdo->query("SELECT 1 as test");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                output("Users Database Connection Test (without database name)", "SUCCESS: Connected to PostgreSQL server, but not to specific database");
            } catch (PDOException $e2) {
                output("Users Database Connection Test (without database name)", "ERROR: " . $e2->getMessage(), true);
            }
        }
        
        // Test Manga DB connection
        try {
            $mangaPdo = new PDO(
                "pgsql:host={$mangaDbConfig['host']};port={$mangaDbConfig['port']};dbname={$mangaDbConfig['database']}",
                $mangaDbConfig['username'],
                $mangaDbConfig['password']
            );
            $mangaPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $mangaPdo->query("SELECT 1 as test");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            output("Manga Database Connection Test", "SUCCESS: Successfully connected to the manga database");
        } catch (PDOException $e) {
            output("Manga Database Connection Test", "ERROR: " . $e->getMessage(), true);
        }
        
    } catch (Exception $e) {
        output("Database Configuration Error", "ERROR: " . $e->getMessage(), true);
    }
    
} catch (Exception $e) {
    output("Laravel Bootstrap Error", "ERROR: " . $e->getMessage(), true);
}

// 4. Test direct PostgreSQL connection using environment variables
try {
    $host = $_ENV['USERS_DB_HOST'] ?? $_SERVER['USERS_DB_HOST'] ?? null;
    $port = $_ENV['USERS_DB_PORT'] ?? $_SERVER['USERS_DB_PORT'] ?? 5432;
    $database = $_ENV['USERS_DB_DATABASE'] ?? $_SERVER['USERS_DB_DATABASE'] ?? null;
    $username = $_ENV['USERS_DB_USERNAME'] ?? $_SERVER['USERS_DB_USERNAME'] ?? null;
    $password = $_ENV['USERS_DB_PASSWORD'] ?? $_SERVER['USERS_DB_PASSWORD'] ?? null;
    
    if ($host && $username) {
        try {
            $dsn = "pgsql:host={$host};port={$port}";
            if ($database) {
                $dsn .= ";dbname={$database}";
            }
            
            $pdo = new PDO($dsn, $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $pdo->query("SELECT 1 as test");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            output("Direct PostgreSQL Connection Test", "SUCCESS: Successfully connected using environment variables");
        } catch (PDOException $e) {
            output("Direct PostgreSQL Connection Test", "ERROR: " . $e->getMessage(), true);
        }
    } else {
        output("Direct PostgreSQL Connection Test", "SKIPPED: Missing required environment variables", true);
    }
} catch (Exception $e) {
    output("Direct PostgreSQL Connection Error", "ERROR: " . $e->getMessage(), true);
}

echo "</div></body></html>";
