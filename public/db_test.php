<?php
/**
 * Direct Database Connection Test Script
 * 
 * This script bypasses Laravel and tests direct connections to PostgreSQL
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
    <title>Direct Database Connection Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .sensitive { color: #999; }
    </style>
</head>
<body>
    <div class='container'>
    <h1>Direct Database Connection Test</h1>";

// IMPORTANT: Replace these values with the actual values from your Render.com dashboard
$host = 'dpg-cnn9nnf109ks73f9ue70-a.oregon-postgres.render.com'; // Replace with actual host
$port = 5432;
$database = 'mangaview_users'; // Replace with actual database name
$username = 'postgres'; // Replace with actual username
$password = 'YOUR_ACTUAL_PASSWORD'; // Replace with actual password

output("Database Connection Parameters", [
    'host' => $host,
    'port' => $port,
    'database' => $database,
    'username' => $username,
    'password' => '********' // Masked for security
]);

// Test direct connection with SSL required
try {
    $dsn = "pgsql:host={$host};port={$port};dbname={$database};sslmode=require";
    output("Connection String (with SSL required)", $dsn);
    
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    output("Connection Status", "SUCCESS: Successfully connected to the database with SSL");
    
    // Test query
    $stmt = $pdo->query("SELECT current_database() as db_name, current_user as username");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    output("Database Information", $result);
    
    // Test creating a table
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS connection_test (
            id SERIAL PRIMARY KEY,
            test_name VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Insert a test record
        $stmt = $pdo->prepare("INSERT INTO connection_test (test_name) VALUES (?)");
        $testName = "Test at " . date('Y-m-d H:i:s');
        $stmt->execute([$testName]);
        
        output("Table Creation", "SUCCESS: Created test table and inserted a record");
        
        // Query the test table
        $stmt = $pdo->query("SELECT * FROM connection_test ORDER BY created_at DESC LIMIT 5");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        output("Recent Test Records", $results);
    } catch (PDOException $e) {
        output("Table Test Error", "ERROR: " . $e->getMessage(), true);
    }
    
} catch (PDOException $e) {
    output("Connection Error (with SSL required)", "ERROR: " . $e->getMessage(), true);
    
    // Try with SSL mode set to prefer
    try {
        $dsn = "pgsql:host={$host};port={$port};dbname={$database};sslmode=prefer";
        output("Connection String (with SSL prefer)", $dsn);
        
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        output("Connection Status (with SSL prefer)", "SUCCESS: Connected to database with SSL prefer mode");
        
        // Test query
        $stmt = $pdo->query("SELECT current_database() as db_name, current_user as username");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        output("Database Information", $result);
    } catch (PDOException $e2) {
        output("Connection Error (with SSL prefer)", "ERROR: " . $e2->getMessage(), true);
        
        // Try without SSL
        try {
            $dsn = "pgsql:host={$host};port={$port};dbname={$database};sslmode=disable";
            output("Connection String (with SSL disabled)", $dsn);
            
            $pdo = new PDO($dsn, $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            output("Connection Status (with SSL disabled)", "SUCCESS: Connected to database with SSL disabled");
            
            // Test query
            $stmt = $pdo->query("SELECT current_database() as db_name, current_user as username");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            output("Database Information", $result);
        } catch (PDOException $e3) {
            output("Connection Error (with SSL disabled)", "ERROR: " . $e3->getMessage(), true);
            
            // Try connecting without database name
            try {
                $dsn = "pgsql:host={$host};port={$port};sslmode=require";
                output("Connection String (without database, with SSL required)", $dsn);
                
                $pdo = new PDO($dsn, $username, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                output("Connection Status (without database)", "SUCCESS: Connected to PostgreSQL server, but not to specific database");
                
                // List available databases
                $stmt = $pdo->query("SELECT datname FROM pg_database WHERE datistemplate = false ORDER BY datname");
                $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                output("Available Databases", $databases);
            } catch (PDOException $e4) {
                output("Connection Error (without database)", "ERROR: " . $e4->getMessage(), true);
            }
        }
    }
}

echo "</div></body></html>";
