<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Manga;
use App\Models\Chapter;
use Illuminate\Support\Facades\DB;

// Find the Horimiya manga
$manga = Manga::where('title', 'Horimiya')->first();

if (!$manga) {
    echo "Manga 'Horimiya' not found. Please create it first.\n";
    exit(1);
}

echo "Found manga: {$manga->title} (ID: {$manga->id})\n";

// Create test chapters
$chapterData = [
    [
        'chapter_number' => 1,
        'title' => 'A Chance Meeting',
        'content' => 'This is the first chapter of Horimiya where Hori and Miyamura meet for the first time.'
    ],
    [
        'chapter_number' => 2,
        'title' => 'Different Faces',
        'content' => 'Hori and Miyamura learn more about each other\'s different personas at school and at home.'
    ],
    [
        'chapter_number' => 3,
        'title' => 'Growing Friendship',
        'content' => 'The friendship between Hori and Miyamura continues to develop as they spend more time together.'
    ],
    [
        'chapter_number' => 4,
        'title' => 'New Friends',
        'content' => 'Ishikawa and Yoshikawa are introduced and become part of Hori and Miyamura\'s circle.'
    ],
    [
        'chapter_number' => 5,
        'title' => 'Feelings Emerge',
        'content' => 'Hori begins to realize her feelings for Miyamura might be more than friendship.'
    ],
];

$createdCount = 0;

foreach ($chapterData as $data) {
    // Check if chapter already exists
    $existingChapter = Chapter::where('manga_id', $manga->id)
        ->where('chapter_number', $data['chapter_number'])
        ->first();
    
    if ($existingChapter) {
        echo "Chapter {$data['chapter_number']} already exists, skipping.\n";
        continue;
    }
    
    // Create new chapter
    $chapter = new Chapter();
    $chapter->manga_id = $manga->id;
    $chapter->chapter_number = $data['chapter_number'];
    $chapter->title = $data['title'];
    $chapter->content = $data['content'];
    $chapter->save();
    
    echo "Created chapter {$chapter->chapter_number}: {$chapter->title}\n";
    $createdCount++;
}

// Update manga total chapters count
$totalChapters = Chapter::where('manga_id', $manga->id)->count();
$manga->total_chapters = $totalChapters;
$manga->save();

echo "\nTotal chapters created: {$createdCount}\n";
echo "Updated manga total chapters to: {$manga->total_chapters}\n";
