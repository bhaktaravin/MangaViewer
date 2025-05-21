<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Vite Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration is used to handle Vite assets in different environments.
    | In production, we'll disable Vite and use CDN resources instead.
    |
    */
    
    'use_manifest' => env('VITE_USE_MANIFEST', !app()->environment('production')),
];
