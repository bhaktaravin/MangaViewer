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

echo "Updating publication dates for manga: {$manga->title} (ID: {$manga->id})\n";

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

// Extract publication dates
$published = $mangaDetails['published'] ?? null;
$fromDate = null;
$toDate = null;

if ($published && isset($published['prop'])) {
    $fromProp = $published['prop']['from'] ?? null;
    $toProp = $published['prop']['to'] ?? null;
    
    if ($fromProp && isset($fromProp['year']) && isset($fromProp['month']) && isset($fromProp['day'])) {
        $fromDate = sprintf('%04d-%02d-%02d', $fromProp['year'], $fromProp['month'], $fromProp['day']);
    }
    
    if ($toProp && isset($toProp['year']) && isset($toProp['month']) && isset($toProp['day'])) {
        $toDate = sprintf('%04d-%02d-%02d', $toProp['year'], $toProp['month'], $toProp['day']);
    }
}

// Update the manga publication dates
$manga->published_from = $fromDate;
$manga->published_to = $toDate;
$manga->save();

echo "Publication dates updated successfully:\n";
echo "Published from: " . ($fromDate ?? 'Unknown') . "\n";
echo "Published to: " . ($toDate ?? 'Unknown') . "\n";
