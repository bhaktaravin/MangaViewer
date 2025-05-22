<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;

class ViteFallbackServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Only run in production
        if (!app()->environment('production')) {
            return;
        }

        // Create a fake manifest file if it doesn't exist
        $manifestPath = public_path('build/manifest.json');
        if (!File::exists($manifestPath)) {
            $directory = dirname($manifestPath);
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            // Create a minimal manifest with common entries
            $manifest = [
                'resources/css/app.css' => [
                    'file' => 'app.css',
                    'src' => 'resources/css/app.css'
                ],
                'resources/js/app.js' => [
                    'file' => 'app.js',
                    'src' => 'resources/js/app.js'
                ]
            ];

            File::put($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT));
        }

        // Register a custom Blade directive for assets
        Blade::directive('viteAssets', function () {
            return '<?php echo app()->environment("production") 
                ? "<script src=\"https://cdn.tailwindcss.com\"></script>" 
                : app("\\Illuminate\\Foundation\\Vite")([\'resources/css/app.css\', \'resources/js/app.js\']); ?>';
        });
    }
}
