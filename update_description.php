<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Manga;
use Illuminate\Support\Facades\Http;

// Get the manga
$manga = Manga::where('title', 'Horimiya')->first();

if (!$manga) {
    echo "Manga 'Horimiya' not found.\n";
    exit(1);
}

echo "Updating description for manga: {$manga->title} (ID: {$manga->id})\n";

// Search for the manga on MyAnimeList
$searchResponse = Http::get('https://api.jikan.moe/v4/manga', [
    'q' => $manga->title,
    'limit' => 1
]);

if (!$searchResponse->successful()) {
    echo "Failed to search manga on MyAnimeList\n";
    exit(1);
}

$searchData = $searchResponse->json();
$results = $searchData['data'] ?? [];

if (empty($results)) {
    echo "No results found on MyAnimeList\n";
    exit(1);
}

$mangaDetails = $results[0];
$description = $mangaDetails['synopsis'] ?? '';

if (empty($description)) {
    echo "No description found on MyAnimeList\n";
    exit(1);
}

// Update the manga description
$manga->description = $description;
$manga->save();

echo "Description updated successfully:\n\n";
echo $description . "\n";
