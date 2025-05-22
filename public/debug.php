<?php
/**
 * Laravel Error Debugging Script
 * 
 * This script helps identify errors in a Laravel application by:
 * 1. Checking environment configuration
 * 2. Testing database connectivity
 * 3. Verifying key Laravel components
 * 4. Logging detailed error information
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
    <title>Laravel Debug Information</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class='container'>
    <h1>Laravel Application Debug Information</h1>";

try {
    // 1. Check if we can access the Laravel application
    require __DIR__.'/../vendor/autoload.php';
    output("Autoloader", "Successfully loaded the Composer autoloader");
    
    // 2. Check environment file
    if (file_exists(__DIR__.'/../.env')) {
        $envContent = file_get_contents(__DIR__.'/../.env');
        $envLines = explode("\n", $envContent);
        $safeEnv = [];
        
        foreach ($envLines as $line) {
            if (empty(trim($line)) || strpos($line, '#') === 0) {
                continue;
            }
            
            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                // Mask sensitive values
                if (in_array($key, ['APP_KEY', 'DB_PASSWORD', 'MAIL_PASSWORD', 'AWS_SECRET_ACCESS_KEY'])) {
                    $safeEnv[$key] = '[MASKED]';
                } else {
                    $safeEnv[$key] = trim($parts[1]);
                }
            }
        }
        
        output("Environment Configuration", $safeEnv);
    } else {
        output("Environment Configuration", "No .env file found!", true);
    }
    
    // 3. Bootstrap Laravel application
    $app = require_once __DIR__.'/../bootstrap/app.php';
    output("Application Bootstrap", "Successfully bootstrapped the Laravel application");
    
    // 4. Run the application
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    output("Kernel", "Successfully created the HTTP kernel");
    
    // Important: Bootstrap the application before using any facades
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    output("Application", "Successfully bootstrapped the Laravel console kernel");
    
    // 5. Check database connection
    try {
        // Use the application container directly instead of facades
        $connection = $app['db']->connection();
        $driver = $connection->getDriverName();
        output("Database Connection", "Successfully connected to the database using driver: " . $driver);
        
        // Get database configuration
        $config = $connection->getConfig();
        $safeConfig = $config;
        if (isset($safeConfig['password'])) {
            $safeConfig['password'] = '[MASKED]';
        }
        output("Database Configuration", $safeConfig);
        
        // Check tables if connection is successful
        try {
            $tables = $connection->getDoctrineSchemaManager()->listTableNames();
            output("Database Tables", $tables);
            
            // 6. Check users table if it exists
            if (in_array('users', $tables)) {
                $usersCount = $app['db']->table('users')->count();
                output("Users Table", "Found {$usersCount} users in the database");
                
                // Get first user (without sensitive info)
                if ($usersCount > 0) {
                    $firstUser = $app['db']->table('users')->select('id', 'name', 'email', 'created_at')->first();
                    output("First User", $firstUser);
                }
            }
        } catch (Exception $e) {
            output("Database Schema Error", "Failed to list tables: " . $e->getMessage(), true);
        }
    } catch (Exception $e) {
        output("Database Error", "Failed to connect to the database: " . $e->getMessage(), true);
        
        // Check if SQLite file exists (if using SQLite)
        $dbConnection = $app['config']->get('database.default');
        if ($dbConnection === 'sqlite') {
            $dbPath = $app['config']->get('database.connections.sqlite.database');
            if ($dbPath !== ':memory:') {
                $fullPath = realpath($dbPath) ?: $dbPath;
                if (file_exists($fullPath)) {
                    output("SQLite Database", "File exists at: {$fullPath}");
                    output("SQLite Permissions", "File permissions: " . substr(sprintf('%o', fileperms($fullPath)), -4));
                    output("SQLite Size", "File size: " . filesize($fullPath) . " bytes");
                } else {
                    output("SQLite Database", "File does not exist at: {$fullPath}", true);
                    
                    // Try to create the SQLite file
                    try {
                        $directory = dirname($fullPath);
                        if (!is_dir($directory)) {
                            mkdir($directory, 0755, true);
                            output("SQLite Directory", "Created directory: {$directory}");
                        }
                        
                        if (touch($fullPath)) {
                            output("SQLite Database", "Created empty database file at: {$fullPath}");
                        } else {
                            output("SQLite Database", "Failed to create database file at: {$fullPath}", true);
                        }
                    } catch (Exception $e) {
                        output("SQLite Creation Error", $e->getMessage(), true);
                    }
                }
            } else {
                output("SQLite Database", "Using in-memory database");
            }
        }
    }
    
    // 7. Check view files that might be causing issues
    $viewPaths = [
        'welcome' => resource_path('views/welcome.blade.php'),
        'home' => resource_path('views/home.blade.php'),
        'dashboard' => resource_path('views/dashboard.blade.php'),
        'app' => resource_path('views/layouts/app.blade.php'),
        'navigation' => resource_path('views/layouts/navigation.blade.php'),
    ];
    
    $viewResults = [];
    foreach ($viewPaths as $name => $path) {
        if (file_exists($path)) {
            $content = file_get_contents($path);
            // Look for potential null access patterns
            $nullAccessPatterns = [
                'Auth::user()->name',
                '->name',
                '$user->name',
                '?->name',
            ];
            
            $matches = [];
            foreach ($nullAccessPatterns as $pattern) {
                if (strpos($content, $pattern) !== false) {
                    $matches[] = $pattern;
                }
            }
            
            $viewResults[$name] = [
                'exists' => true,
                'potential_null_access' => !empty($matches) ? $matches : 'None found'
            ];
        } else {
            $viewResults[$name] = ['exists' => false];
        }
    }
    output("View Files Analysis", $viewResults);
    
    // 8. Check service providers that might be causing issues
    try {
        $providers = $app->getLoadedProviders();
        output("Loaded Service Providers", array_keys($providers));
    } catch (Exception $e) {
        output("Service Provider Error", $e->getMessage(), true);
    }
    
    // 9. Check for common middleware that might access Auth::user()
    try {
        $router = $app->make('router');
        $middleware = $router->getMiddleware();
        output("Global Middleware", $middleware);
    } catch (Exception $e) {
        output("Middleware Error", $e->getMessage(), true);
    }
    
    // 10. Check for Vite manifest
    $viteManifestPath = public_path('build/manifest.json');
    if (file_exists($viteManifestPath)) {
        $manifestContent = file_get_contents($viteManifestPath);
        $manifest = json_decode($manifestContent, true);
        output("Vite Manifest", "Found at: {$viteManifestPath}");
        output("Manifest Content", $manifest);
    } else {
        output("Vite Manifest", "Not found at: {$viteManifestPath}", true);
        
        // Check if we're using CDN fallback
        $appBladePath = resource_path('views/layouts/app.blade.php');
        $guestBladePath = resource_path('views/layouts/guest.blade.php');
        
        $usingCDN = false;
        if (file_exists($appBladePath)) {
            $appBladeContent = file_get_contents($appBladePath);
            if (strpos($appBladeContent, 'cdn.tailwindcss.com') !== false) {
                $usingCDN = true;
            }
        }
        
        if (file_exists($guestBladePath)) {
            $guestBladeContent = file_get_contents($guestBladePath);
            if (strpos($guestBladeContent, 'cdn.tailwindcss.com') !== false) {
                $usingCDN = true;
            }
        }
        
        if ($usingCDN) {
            output("CSS Fallback", "Using Tailwind CSS CDN as fallback");
        } else {
            output("CSS Fallback", "No fallback detected for missing Vite manifest", true);
        }
    }
    
} catch (Exception $e) {
    output("Critical Error", $e->getMessage() . "\n" . $e->getTraceAsString(), true);
}

echo "</div></body></html>";
