<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for database operations. This is
    | the connection which will be utilized unless another connection
    | is explicitly specified when you execute a query / statement.
    |
    */

    'default' => env('DB_CONNECTION', 'users_db'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Below are all of the database connections defined for your application.
    | An example configuration is provided for each database system which
    | is supported by Laravel. You're free to add / remove connections.
    |
    */

    'connections' => [

        'users_db' => [
            'driver' => 'pgsql',
            // IMPORTANT: Replace these values with the actual values from your Render.com dashboard
            'host' => 'dpg-cnn9nnf109ks73f9ue70-a.oregon-postgres.render.com', // Replace with actual host
            'port' => 5432,
            'database' => 'mangaview_users', // Replace with actual database name
            'username' => 'postgres', // Replace with actual username
            'password' => 'YOUR_ACTUAL_PASSWORD', // Replace with actual password
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'require', // Changed from 'prefer' to 'require' for Render.com
            'options' => [
                PDO::ATTR_PERSISTENT => false,
            ],
        ],
        
        'manga_db' => [
            'driver' => 'pgsql',
            // IMPORTANT: Replace these values with the actual values from your Render.com dashboard
            'host' => 'dpg-cnn9nnf109ks73f9ue70-a.oregon-postgres.render.com', // Replace with actual host
            'port' => 5432,
            'database' => 'mangaview_manga', // Replace with actual database name
            'username' => 'postgres', // Replace with actual username
            'password' => 'YOUR_ACTUAL_PASSWORD', // Replace with actual password
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'require', // Changed from 'prefer' to 'require' for Render.com
            'options' => [
                PDO::ATTR_PERSISTENT => false,
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run on the database.
    |
    */

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as Memcached. You may define your connection settings here.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
            'persistent' => env('REDIS_PERSISTENT', false),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];
