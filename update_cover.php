<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get the manga ID from the database
$manga = App\Models\Manga::first();
echo "Manga ID: " . $manga->id . ", Title: " . $manga->title . ", Current cover: " . $manga->cover_image . PHP_EOL;

// Download a new cover image
$service = new App\Services\MyAnimeListService();
$coverPath = $service->downloadCoverImage($manga->title);
echo "New cover path: " . $coverPath . PHP_EOL;

// Update the manga record
$manga->cover_image = $coverPath;
$manga->save();
echo "Manga updated with new cover image" . PHP_EOL;

