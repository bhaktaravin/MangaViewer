<?php
/**
 * Helper file to prevent "Class 'env' does not exist" error during package discovery
 */

// Define the env function if it doesn't exist
if (!function_exists('env')) {
    function env($key, $default = null) {
        return $default;
    }
}

// Define the env class if it doesn't exist
if (!class_exists('env')) {
    class env {}
}
