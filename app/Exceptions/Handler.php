<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Enhanced error logging for null property access
            if (str_contains($e->getMessage(), "Attempt to read property") && 
                str_contains($e->getMessage(), "on null")) {
                
                Log::error('NULL PROPERTY ACCESS ERROR: ' . $e->getMessage());
                Log::error('Error occurred in file: ' . $e->getFile() . ' on line ' . $e->getLine());
                
                // Log the stack trace to help identify the source
                $trace = $e->getTraceAsString();
                Log::error('Stack trace: ' . $trace);
                
                // Log the request information
                if (request()) {
                    Log::error('Request URL: ' . request()->fullUrl());
                    Log::error('Request Method: ' . request()->method());
                    Log::error('User Agent: ' . request()->userAgent());
                    
                    // Log authenticated user if any
                    if (auth()->check()) {
                        Log::error('Authenticated User ID: ' . auth()->id());
                    } else {
                        Log::error('No authenticated user');
                    }
                }
            }
        });
    }
}
