<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get the manga
$manga = App\Models\Manga::first();
echo "Testing chapter fetching for manga: {$manga->title} (ID: {$manga->id})\n";

// Create and use the chapter fetcher service
$chapterFetcher = new App\Services\ChapterFetcherService();
$count = $chapterFetcher->fetchFromMyAnimeList($manga);

if ($count === false) {
    echo "Failed to fetch chapters from MyAnimeList.\n";
} else {
    echo "Successfully imported {$count} new chapters.\n";
    echo "Total chapters now: {$manga->total_chapters}\n";
}

