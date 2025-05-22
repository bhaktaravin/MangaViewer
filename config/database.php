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
            'url' => null, // Don't use URL to avoid parameter mixing
            'host' => env('USERS_DB_HOST') ? trim(env('USERS_DB_HOST')) : 'localhost',
            'port' => env('USERS_DB_PORT') ? intval(env('USERS_DB_PORT')) : 5432,
            'database' => env('USERS_DB_DATABASE') ? trim(env('USERS_DB_DATABASE')) : 'mangaview_users',
            'username' => env('USERS_DB_USERNAME') ? trim(env('USERS_DB_USERNAME')) : 'postgres',
            'password' => env('USERS_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
            'options' => [
                PDO::ATTR_PERSISTENT => false,
            ],
        ],
        
        'manga_db' => [
            'driver' => 'pgsql',
            'url' => null, // Don't use URL to avoid parameter mixing
            'host' => env('MANGA_DB_HOST') ? trim(env('MANGA_DB_HOST')) : 'localhost',
            'port' => env('MANGA_DB_PORT') ? intval(env('MANGA_DB_PORT')) : 5432,
            'database' => env('MANGA_DB_DATABASE') ? trim(env('MANGA_DB_DATABASE')) : 'mangaview_manga',
            'username' => env('MANGA_DB_USERNAME') ? trim(env('MANGA_DB_USERNAME')) : 'postgres',
            'password' => env('MANGA_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
            'options' => [
                PDO::ATTR_PERSISTENT => false,
            ],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => null,
            'host' => env('DB_HOST') ? trim(env('DB_HOST')) : 'localhost',
            'port' => env('DB_PORT') ? intval(env('DB_PORT')) : 5432,
            'database' => env('DB_DATABASE') ? trim(env('DB_DATABASE')) : 'mangaview',
            'username' => env('DB_USERNAME') ? trim(env('DB_USERNAME')) : 'postgres',
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
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
