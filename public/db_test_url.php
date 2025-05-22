<?php
/**
 * Direct Database Connection Test Script Using Connection URL
 * 
 * This script tests connection using the PostgreSQL connection URL format
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
    <title>Database Connection URL Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .sensitive { color: #999; }
    </style>
</head>
<body>
    <div class='container'>
    <h1>Database Connection URL Test</h1>";

// IMPORTANT: Replace with your actual connection URL from Render.com dashboard
$connectionUrl = 'postgres://postgres:YOUR_ACTUAL_PASSWORD@dpg-cnn9nnf109ks73f9ue70-a.oregon-postgres.render.com/mangaview_users';

// Mask password for display
$displayUrl = preg_replace('/(:)([^@]*)(@)/', ':********@', $connectionUrl);
output("Connection URL", $displayUrl);

// Test connection using URL
try {
    $pdo = new PDO($connectionUrl);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    output("Connection Status", "SUCCESS: Successfully connected using connection URL");
    
    // Test query
    $stmt = $pdo->query("SELECT current_database() as db_name, current_user as username");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    output("Database Information", $result);
    
} catch (PDOException $e) {
    output("Connection Error", "ERROR: " . $e->getMessage(), true);
    
    // Try parsing the URL and connecting with individual parameters
    try {
        $parsedUrl = parse_url($connectionUrl);
        $host = $parsedUrl['host'];
        $port = isset($parsedUrl['port']) ? $parsedUrl['port'] : 5432;
        $user = $parsedUrl['user'];
        $pass = $parsedUrl['pass'];
        $dbname = ltrim($parsedUrl['path'], '/');
        
        output("Parsed URL Components", [
            'host' => $host,
            'port' => $port,
            'user' => $user,
            'pass' => '********',
            'dbname' => $dbname
        ]);
        
        // Try different SSL modes
        $sslModes = ['require', 'prefer', 'verify-ca', 'verify-full', 'disable'];
        
        foreach ($sslModes as $sslmode) {
            try {
                $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode={$sslmode}";
                output("Trying Connection with sslmode={$sslmode}", $dsn);
                
                $pdo = new PDO($dsn, $user, $pass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                $stmt = $pdo->query("SELECT 1 as test");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                output("Connection Success with sslmode={$sslmode}", "Successfully connected to the database!");
                
                // If we get here, the connection worked
                break;
            } catch (PDOException $e2) {
                output("Connection Error with sslmode={$sslmode}", "ERROR: " . $e2->getMessage(), true);
            }
        }
    } catch (Exception $e3) {
        output("URL Parsing Error", "ERROR: " . $e3->getMessage(), true);
    }
}

echo "</div></body></html>";
