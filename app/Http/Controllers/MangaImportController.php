<?php

namespace App\Http\Controllers;

use App\Models\Manga;
use App\Models\Chapter;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MangaImportController extends Controller
{
    /**
     * Show the form for importing manga from MangaDex.
     */
    public function showImportForm()
    {
        return view('manga.import');
    }

    /**
     * Search for manga on MangaDex API.
     */
    public function searchMangaDex(Request $request)
    {
        $validated = $request->validate([
            'search' => 'required|string|min:3|max:100',
        ]);

        try {
            $response = Http::get('https://api.mangadex.org/manga', [
                'title' => $validated['search'],
                'limit' => 10,
                'includes' => ['cover_art', 'author'],
            ]);

            if ($response->successful()) {
                $results = $response->json()['data'];
                return view('manga.search_results', compact('results'));
            } else {
                return back()->with('error', 'Failed to search MangaDex. Please try again later.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Import a manga from MangaDex.
     */
    public function importFromMangaDex(Request $request)
    {
        $validated = $request->validate([
            'manga_id' => 'required|string',
        ]);

        try {
            // Log the start of import process
            \Log::info('Starting manga import for ID: ' . $validated['manga_id']);

            // Get manga details
            $mangaResponse = Http::get("https://api.mangadex.org/manga/{$validated['manga_id']}", [
                'includes' => ['cover_art', 'author'],
            ]);

            if (!$mangaResponse->successful()) {
                \Log::error('Failed to fetch manga details: ' . $mangaResponse->status());
                return back()->with('error', 'Failed to fetch manga details from MangaDex. Status: ' . $mangaResponse->status());
            }

            $mangaData = $mangaResponse->json()['data'];
            \Log::info('Manga data fetched successfully');

            // Extract manga information
            $title = $mangaData['attributes']['title']['en'] ?? array_values($mangaData['attributes']['title'])[0];
            $description = $mangaData['attributes']['description']['en'] ?? '';
            $status = $this->mapMangaDexStatus($mangaData['attributes']['status']);

            // Get author name
            $author = 'Unknown';
            foreach ($mangaData['relationships'] as $relationship) {
                if ($relationship['type'] === 'author') {
                    $author = $relationship['attributes']['name'] ?? 'Unknown';
                    break;
                }
            }

            // Get cover image
            $coverFileName = null;
            foreach ($mangaData['relationships'] as $relationship) {
                if ($relationship['type'] === 'cover_art') {
                    $coverFileName = $relationship['attributes']['fileName'] ?? null;
                    break;
                }
            }

            // Download cover image if available
            $coverPath = null;
            if ($coverFileName) {
                $coverUrl = "https://uploads.mangadex.org/covers/{$validated['manga_id']}/{$coverFileName}";
                \Log::info('Downloading cover image from: ' . $coverUrl);

                $coverImage = Http::get($coverUrl);
                if ($coverImage->successful()) {
                    $coverPath = "covers/" . Str::random(40) . ".jpg";
                    $result = Storage::disk('public')->put($coverPath, $coverImage->body());
                    \Log::info('Cover image saved to: ' . $coverPath . ', Result: ' . ($result ? 'Success' : 'Failed'));
                } else {
                    \Log::warning('Failed to download cover image: ' . $coverImage->status());
                }
            }

            // Check if manga already exists in database
            $existingManga = Manga::where('title', $title)->first();
            if ($existingManga) {
                \Log::info('Manga already exists in database: ' . $title);
                return redirect()->route('manga.show', $existingManga)
                    ->with('info', 'This manga already exists in your database.');
            }

            // Create manga record in database
            \Log::info('Creating manga record: ' . $title);
            $manga = Manga::create([
                'title' => $title,
                'description' => $description,
                'author' => $author,
                'status' => $status,
                'cover_image' => $coverPath,
                'total_chapters' => 0,
            ]);

            // Get chapters with pagination to fetch more chapters
            \Log::info('Fetching chapters for manga ID: ' . $validated['manga_id']);
            $allChapters = [];
            $offset = 0;
            $limit = 100; // Maximum allowed by MangaDex API
            $hasMoreChapters = true;
            
            while ($hasMoreChapters) {
                \Log::info("Fetching chapters batch: offset {$offset}, limit {$limit}");
                
                $chaptersResponse = Http::get("https://api.mangadex.org/manga/{$validated['manga_id']}/feed", [
                    'limit' => $limit,
                    'offset' => $offset,
                    'translatedLanguage' => ['en'],
                    'order' => ['chapter' => 'asc'],
                ]);
                
                if (!$chaptersResponse->successful()) {
                    \Log::warning("Failed to fetch chapters at offset {$offset}: " . $chaptersResponse->status());
                    break;
                }
                
                $chaptersData = $chaptersResponse->json()['data'];
                $total = $chaptersResponse->json()['total'];
                
                \Log::info("Fetched " . count($chaptersData) . " chapters, total available: {$total}");
                
                $allChapters = array_merge($allChapters, $chaptersData);
                $offset += $limit;
                
                // Check if we've fetched all chapters
                if (count($allChapters) >= $total || count($chaptersData) == 0) {
                    $hasMoreChapters = false;
                }
                
                // Add a small delay to avoid rate limiting
                usleep(500000); // 0.5 seconds
            }
            
            \Log::info("Total chapters fetched: " . count($allChapters));
            
            $chapterCount = 0;
            $pageCount = 0;
            
            if (count($allChapters) > 0) {
                // Limit to 10 chapters for initial import to avoid overwhelming the server
                // Remove this line if you want to import all chapters
                $processChapters = array_slice($allChapters, 0, 10);
                \Log::info("Processing first 10 chapters out of " . count($allChapters) . " total");
                
                foreach ($processChapters as $chapterData) {
                    $chapterNumber = $chapterData['attributes']['chapter'] ?? '0';
                    $chapterTitle = $chapterData['attributes']['title'] ?? "Chapter {$chapterNumber}";

                    \Log::info('Processing chapter: ' . $chapterNumber . ' - ' . $chapterTitle);

                    $chapter = $manga->chapters()->create([
                        'title' => $chapterTitle,
                        'chapter_number' => (int)$chapterNumber,
                    ]);

                    $chapterCount++;

                    // Get chapter pages
                    $chapterId = $chapterData['id'];
                    \Log::info('Fetching pages for chapter ID: ' . $chapterId);

                    $pagesResponse = Http::get("https://api.mangadex.org/at-home/server/{$chapterId}");

                    if ($pagesResponse->successful()) {
                        $baseUrl = $pagesResponse->json()['baseUrl'];
                        $hash = $pagesResponse->json()['chapter']['hash'];
                        $pageFiles = $pagesResponse->json()['chapter']['data'];

                        \Log::info('Found ' . count($pageFiles) . ' pages for chapter ' . $chapterNumber);

                        // Create directory if it doesn't exist
                        $dirPath = "chapters/{$manga->id}/{$chapter->id}";
                        if (!Storage::disk('public')->exists($dirPath)) {
                            Storage::disk('public')->makeDirectory($dirPath);
                            \Log::info('Created directory: ' . $dirPath);
                        }

                        // Limit to 5 pages per chapter for testing
                        $pageFiles = array_slice($pageFiles, 0, 5);

                        foreach ($pageFiles as $index => $pageFile) {
                            $pageUrl = "{$baseUrl}/data/{$hash}/{$pageFile}";
                            \Log::info('Downloading page ' . ($index + 1) . ' from: ' . $pageUrl);

                            $pageImage = Http::get($pageUrl);

                            if ($pageImage->successful()) {
                                $pagePath = "{$dirPath}/" . Str::random(20) . ".jpg";
                                $result = Storage::disk('public')->put($pagePath, $pageImage->body());
                                \Log::info('Page saved to: ' . $pagePath . ', Result: ' . ($result ? 'Success' : 'Failed'));

                                if ($result) {
                                    $chapter->pages()->create([
                                        'page_number' => $index + 1,
                                        'image_path' => $pagePath,
                                    ]);
                                    $pageCount++;
                                }
                            } else {
                                \Log::warning('Failed to download page: ' . $pageImage->status());
                            }
                        }
                    } else {
                        \Log::warning('Failed to fetch pages for chapter: ' . $pagesResponse->status());
                    }
                }

                // Update total chapters count
                $manga->total_chapters = $manga->chapters()->count();
                $manga->save();
                \Log::info('Updated manga with total chapters: ' . $manga->total_chapters);
            } else {
                \Log::warning('Failed to fetch chapters: ' . $chaptersResponse->status());
            }

            \Log::info('Import completed successfully. Downloaded ' . $chapterCount . ' chapters with ' . $pageCount . ' pages.');

            return redirect()->route('manga.show', $manga)
                ->with('success', 'Manga successfully imported from MangaDex! Downloaded ' . $chapterCount . ' chapters with ' . $pageCount . ' pages.');

        } catch (\Exception $e) {
            \Log::error('Exception during import: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return back()->with('error', 'An error occurred during import: ' . $e->getMessage());
        }
    }

    /**
     * Map MangaDex status to our application status.
     */
    private function mapMangaDexStatus($status)
    {
        return match ($status) {
            'completed' => 'completed',
            'ongoing' => 'ongoing',
            'hiatus' => 'hiatus',
            default => 'ongoing',
        };
    }

    /**
     * Show the form for manual manga import.
     */
    public function showManualImportForm()
    {
        return view('manga.manual_import');
    }

    /**
     * Process manual manga import.
     */
    public function processManualImport(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'author' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:ongoing,completed,hiatus',
            'cover_image' => 'nullable|image|max:2048',
            'chapter_files' => 'required|array',
            'chapter_files.*' => 'required|file|mimes:zip,rar,7z',
            'chapter_titles' => 'required|array',
            'chapter_titles.*' => 'required|string|max:255',
            'chapter_numbers' => 'required|array',
            'chapter_numbers.*' => 'required|integer|min:0',
        ]);

        try {
            // Create manga record
            $manga = new Manga([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'author' => $validated['author'] ?? null,
                'status' => $validated['status'] ?? 'ongoing',
            ]);

            // Handle cover image
            if ($request->hasFile('cover_image')) {
                $path = $request->file('cover_image')->store('covers', 'public');
                $manga->cover_image = $path;
            }

            $manga->save();

            // Process chapter files
            foreach ($validated['chapter_files'] as $index => $chapterFile) {
                $chapterNumber = $validated['chapter_numbers'][$index];
                $chapterTitle = $validated['chapter_titles'][$index];

                // Create chapter record
                $chapter = $manga->chapters()->create([
                    'title' => $chapterTitle,
                    'chapter_number' => $chapterNumber,
                ]);

                // Extract and process chapter files (simplified for this example)
                // In a real implementation, you'd need to extract the zip/rar files
                // and process the images inside

                // For now, we'll just create a placeholder page
                $chapter->pages()->create([
                    'page_number' => 1,
                    'image_path' => 'placeholder.jpg', // This would be replaced with actual extracted images
                ]);
            }

            // Update total chapters count
            $manga->total_chapters = $manga->chapters()->count();
            $manga->save();

            return redirect()->route('manga.show', $manga)
                ->with('success', 'Manga successfully imported!');

        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred during import: ' . $e->getMessage());
        }
    }

   /**
     * Test image download from MangaDex.
     */
    public function testImageDownload()
    {
        try {
            // Test if we can download and store an image
            $testUrl = "https://uploads.mangadex.org/covers/32d76d19-8a05-4db0-9fc2-e0b0648fe9d0/4cd32f19-a3f1-4c71-8f60-05c2450b0dc0.jpg";
            $testImage = Http::get($testUrl);

            if ($testImage->successful()) {
                $testPath = "test_images/test_" . time() . ".jpg";
                Storage::disk('public')->put($testPath, $testImage->body());

                return view('manga.test_image', [
                    'imagePath' => $testPath,
                    'success' => true,
                    'message' => 'Image downloaded successfully!'
                ]);
            } else {
                return view('manga.test_image', [
                    'success' => false,
                    'message' => 'Failed to download image. Status: ' . $testImage->status()
                ]);
            }
        } catch (\Exception $e) {
            return view('manga.test_image', [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ]);
        }
    }

}
