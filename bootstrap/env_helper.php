<?php
/**
 * Temporary env() helper function to prevent "Class 'env' does not exist" error
 * during Composer's package discovery phase.
 */
if (!function_exists('env')) {
    function env($key, $default = null) {
        return $default;
    }
}
