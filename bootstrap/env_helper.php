<?php
/**
 * Helper file to provide env() function for Laravel
 */

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        // In the helper, just return the default value
        return $default;
    }
}
