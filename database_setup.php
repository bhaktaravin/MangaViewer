<?php
// database_setup.php - Script to create necessary tables if they don't exist

// Bootstrap the Laravel application
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Set up logging
use Illuminate\Support\Facades\Log;
Log::info('Starting database setup script');

// First, try to create the databases if they don't exist
try {
    // Get connection parameters from environment
    $host = env('USERS_DB_HOST', '127.0.0.1');
    $port = env('USERS_DB_PORT', '5432');
    $username = env('USERS_DB_USERNAME', 'postgres');
    $password = env('USERS_DB_PASSWORD', '');
    $usersDb = env('USERS_DB_DATABASE', 'mangaview_users');
    $mangaDb = env('MANGA_DB_DATABASE', 'mangaview_manga');
    
    Log::info("Attempting to connect to PostgreSQL at {$host}:{$port} to create databases");
    
    // Connect to PostgreSQL without specifying a database (connect to 'postgres' default database)
    $dsn = "pgsql:host={$host};port={$port};dbname=postgres";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if users database exists
    $stmt = $pdo->query("SELECT 1 FROM pg_database WHERE datname = '{$usersDb}'");
    $exists = $stmt->fetchColumn();
    
    if (!$exists) {
        Log::info("Creating users database: {$usersDb}");
        $pdo->exec("CREATE DATABASE {$usersDb}");
        echo "Created users database: {$usersDb}\n";
    } else {
        Log::info("Users database {$usersDb} already exists");
    }
    
    // Check if manga database exists
    $stmt = $pdo->query("SELECT 1 FROM pg_database WHERE datname = '{$mangaDb}'");
    $exists = $stmt->fetchColumn();
    
    if (!$exists) {
        Log::info("Creating manga database: {$mangaDb}");
        $pdo->exec("CREATE DATABASE {$mangaDb}");
        echo "Created manga database: {$mangaDb}\n";
    } else {
        Log::info("Manga database {$mangaDb} already exists");
    }
    
} catch (\Exception $e) {
    Log::error('Failed to create databases: ' . $e->getMessage());
    echo "Failed to create databases: " . $e->getMessage() . "\n";
}

// Function to check if a table exists
function tableExists($connection, $table) {
    try {
        return \Illuminate\Support\Facades\Schema::connection($connection)->hasTable($table);
    } catch (\Exception $e) {
        Log::error("Error checking if table {$table} exists on {$connection}: " . $e->getMessage());
        return false;
    }
}

// Function to create users database tables
function createUsersTables() {
    Log::info('Checking users database tables...');
    
    // Check and create users table
    if (!tableExists('users_db', 'users')) {
        Log::info('Creating users table');
        try {
            \Illuminate\Support\Facades\Schema::connection('users_db')->create('users', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
            });
            Log::info('Users table created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create users table: ' . $e->getMessage());
        }
    } else {
        Log::info('Users table already exists');
    }
    
    // Check and create password_reset_tokens table
    if (!tableExists('users_db', 'password_reset_tokens')) {
        Log::info('Creating password_reset_tokens table');
        try {
            \Illuminate\Support\Facades\Schema::connection('users_db')->create('password_reset_tokens', function ($table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
            Log::info('Password reset tokens table created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create password_reset_tokens table: ' . $e->getMessage());
        }
    }
    
    // Check and create sessions table
    if (!tableExists('users_db', 'sessions')) {
        Log::info('Creating sessions table');
        try {
            \Illuminate\Support\Facades\Schema::connection('users_db')->create('sessions', function ($table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
            Log::info('Sessions table created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create sessions table: ' . $e->getMessage());
        }
    }
    
    // Check and create failed_jobs table
    if (!tableExists('users_db', 'failed_jobs')) {
        Log::info('Creating failed_jobs table');
        try {
            \Illuminate\Support\Facades\Schema::connection('users_db')->create('failed_jobs', function ($table) {
                $table->id();
                $table->string('uuid')->unique();
                $table->text('connection');
                $table->text('queue');
                $table->longText('payload');
                $table->longText('exception');
                $table->timestamp('failed_at')->useCurrent();
            });
            Log::info('Failed jobs table created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create failed_jobs table: ' . $e->getMessage());
        }
    }
    
    // Check and create personal_access_tokens table
    if (!tableExists('users_db', 'personal_access_tokens')) {
        Log::info('Creating personal_access_tokens table');
        try {
            \Illuminate\Support\Facades\Schema::connection('users_db')->create('personal_access_tokens', function ($table) {
                $table->id();
                $table->morphs('tokenable');
                $table->string('name');
                $table->string('token', 64)->unique();
                $table->text('abilities')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
            Log::info('Personal access tokens table created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create personal_access_tokens table: ' . $e->getMessage());
        }
    }
    
    // Check and create cache table
    if (!tableExists('users_db', 'cache')) {
        Log::info('Creating cache table');
        try {
            \Illuminate\Support\Facades\Schema::connection('users_db')->create('cache', function ($table) {
                $table->string('key')->primary();
                $table->mediumText('value');
                $table->integer('expiration');
            });
            Log::info('Cache table created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create cache table: ' . $e->getMessage());
        }
    }
    
    // Check and create cache_locks table
    if (!tableExists('users_db', 'cache_locks')) {
        Log::info('Creating cache_locks table');
        try {
            \Illuminate\Support\Facades\Schema::connection('users_db')->create('cache_locks', function ($table) {
                $table->string('key')->primary();
                $table->string('owner');
                $table->integer('expiration');
            });
            Log::info('Cache locks table created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create cache_locks table: ' . $e->getMessage());
        }
    }
    
    // Check and create migrations table
    if (!tableExists('users_db', 'migrations')) {
        Log::info('Creating migrations table');
        try {
            \Illuminate\Support\Facades\Schema::connection('users_db')->create('migrations', function ($table) {
                $table->id();
                $table->string('migration');
                $table->integer('batch');
            });
            Log::info('Migrations table created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create migrations table: ' . $e->getMessage());
        }
    }
}

// Function to create manga database tables
function createMangaTables() {
    Log::info('Checking manga database tables...');
    
    // Check and create mangas table
    if (!tableExists('manga_db', 'mangas')) {
        Log::info('Creating mangas table');
        try {
            \Illuminate\Support\Facades\Schema::connection('manga_db')->create('mangas', function ($table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('cover_image')->nullable();
                $table->string('author')->nullable();
                $table->string('status')->default('ongoing');
                $table->integer('total_chapters')->default(0);
                $table->date('published_from')->nullable();
                $table->date('published_to')->nullable();
                $table->timestamps();
            });
            Log::info('Mangas table created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create mangas table: ' . $e->getMessage());
        }
    }
    
    // Check and create chapters table
    if (!tableExists('manga_db', 'chapters')) {
        Log::info('Creating chapters table');
        try {
            \Illuminate\Support\Facades\Schema::connection('manga_db')->create('chapters', function ($table) {
                $table->id();
                $table->foreignId('manga_id');
                $table->string('title');
                $table->integer('chapter_number');
                $table->text('content')->nullable();
                $table->string('file_path')->nullable();
                $table->timestamps();
            });
            Log::info('Chapters table created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create chapters table: ' . $e->getMessage());
        }
    }
    
    // Check and create pages table
    if (!tableExists('manga_db', 'pages')) {
        Log::info('Creating pages table');
        try {
            \Illuminate\Support\Facades\Schema::connection('manga_db')->create('pages', function ($table) {
                $table->id();
                $table->foreignId('chapter_id');
                $table->integer('page_number');
                $table->string('image_path');
                $table->timestamps();
            });
            Log::info('Pages table created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create pages table: ' . $e->getMessage());
        }
    }
    
    // Check and create migrations table
    if (!tableExists('manga_db', 'migrations')) {
        Log::info('Creating migrations table');
        try {
            \Illuminate\Support\Facades\Schema::connection('manga_db')->create('migrations', function ($table) {
                $table->id();
                $table->string('migration');
                $table->integer('batch');
            });
            Log::info('Migrations table created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create migrations table: ' . $e->getMessage());
        }
    }
}

// Main execution
try {
    // Check database connections
    Log::info('Testing database connections...');
    
    try {
        \Illuminate\Support\Facades\DB::connection('users_db')->getPdo();
        Log::info('Users database connection successful');
        createUsersTables();
    } catch (\Exception $e) {
        Log::error('Users database connection failed: ' . $e->getMessage());
        echo "Users database connection failed: " . $e->getMessage() . "\n";
    }
    
    try {
        \Illuminate\Support\Facades\DB::connection('manga_db')->getPdo();
        Log::info('Manga database connection successful');
        createMangaTables();
    } catch (\Exception $e) {
        Log::error('Manga database connection failed: ' . $e->getMessage());
        echo "Manga database connection failed: " . $e->getMessage() . "\n";
    }
    
    Log::info('Database setup script completed');
    echo "Database setup script completed. Check the logs for details.\n";
    
} catch (\Exception $e) {
    Log::error('Error in database setup script: ' . $e->getMessage());
    echo "Error in database setup script: " . $e->getMessage() . "\n";
}
