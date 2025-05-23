<?php
/**
 * PHP Extension Check
 * 
 * This script checks for the presence of required PHP extensions,
 * particularly those needed for PostgreSQL connectivity.
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
    <title>PHP Extension Check</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class='container'>
    <h1>PHP Extension Check</h1>";

// Check for PDO extension
if (extension_loaded('PDO')) {
    output("PDO Extension", "<span class='success'>✓ PDO extension is loaded</span>");
    
    // Check available PDO drivers
    $drivers = PDO::getAvailableDrivers();
    output("Available PDO Drivers", $drivers);
    
    // Check specifically for pgsql driver
    if (in_array('pgsql', $drivers)) {
        output("PostgreSQL PDO Driver", "<span class='success'>✓ PostgreSQL PDO driver (pdo_pgsql) is available</span>");
    } else {
        output("PostgreSQL PDO Driver", "<span class='error'>✗ PostgreSQL PDO driver (pdo_pgsql) is NOT available</span>", true);
    }
} else {
    output("PDO Extension", "<span class='error'>✗ PDO extension is NOT loaded</span>", true);
}

// Check for pgsql extension
if (extension_loaded('pgsql')) {
    output("PostgreSQL Extension", "<span class='success'>✓ PostgreSQL extension (pgsql) is loaded</span>");
} else {
    output("PostgreSQL Extension", "<span class='error'>✗ PostgreSQL extension (pgsql) is NOT loaded</span>", true);
}

// Check for pdo_pgsql extension
if (extension_loaded('pdo_pgsql')) {
    output("PostgreSQL PDO Extension", "<span class='success'>✓ PostgreSQL PDO extension (pdo_pgsql) is loaded</span>");
} else {
    output("PostgreSQL PDO Extension", "<span class='error'>✗ PostgreSQL PDO extension (pdo_pgsql) is NOT loaded</span>", true);
}

// List all loaded extensions
$extensions = get_loaded_extensions();
sort($extensions);
output("All Loaded Extensions", $extensions);

// PHP version and configuration
output("PHP Version", phpversion());
output("PHP Configuration File (php.ini)", php_ini_loaded_file());

echo "</div></body></html>";
