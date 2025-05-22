<?php

/**
 * Global helper functions for the MangaView application
 */

// Import common Laravel facades to prevent "Class not found" errors
if (!function_exists('register_common_facades')) {
    function register_common_facades() {
        // Auth facade
        if (!class_exists('Auth')) {
            class_alias(\Illuminate\Support\Facades\Auth::class, 'Auth');
        }
        
        // Route facade
        if (!class_exists('Route')) {
            class_alias(\Illuminate\Support\Facades\Route::class, 'Route');
        }
        
        // DB facade
        if (!class_exists('DB')) {
            class_alias(\Illuminate\Support\Facades\DB::class, 'DB');
        }
        
        // Session facade
        if (!class_exists('Session')) {
            class_alias(\Illuminate\Support\Facades\Session::class, 'Session');
        }
        
        // URL facade
        if (!class_exists('URL')) {
            class_alias(\Illuminate\Support\Facades\URL::class, 'URL');
        }
        
        // Blade facade
        if (!class_exists('Blade')) {
            class_alias(\Illuminate\Support\Facades\Blade::class, 'Blade');
        }
    }
}

// Register facades
register_common_facades();
