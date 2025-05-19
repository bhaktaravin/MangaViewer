<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$service = new App\Services\MyAnimeListService();
$coverPath = $service->downloadCoverImage('Horimiya');
echo "Cover path: " . $coverPath . PHP_EOL;

