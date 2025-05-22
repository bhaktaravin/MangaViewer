<?php

namespace App\Http\Controllers;

use App\Models\Manga;
use App\Models\Chapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class JikanImportController extends Controller
{
    /**
     * Show the form for importing manga from Jikan/MyAnimeList.
     */
    public function showImportForm()
    {
        return view('manga.jikan_import');
    }

    /**
     * Search for manga on Jikan API.
     */
    public function searchJikan(Request $request)
    {
        $validated = $request->validate([
            'search' => 'required|string|min:3|max:100',
        ]);

        try {
            Log::info('Searching MyAnimeList for: ' . $validated['search']);
            
            // Use Guzzle client with better error handling
            $client = new \GuzzleHttp\Client([
                'timeout' => 15,
                'connect_timeout' => 5,
                'http_errors' => false,
            ]);
            
            $response = $client->get('https://api.jikan.moe/v4/manga', [
                'query' => [
                    'q' => $validated['search'],
                    'limit' => 10,
                ]
            ]);
            
            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            
            Log::info('Jikan API response status: ' . $statusCode);
            
            if ($statusCode >= 200 && $statusCode < 300) {
                $data = json_decode($body, true);
                
                if (isset($data['data']) && is_array($data['data'])) {
                    $results = $data['data'];
                    Log::info('Found ' . count($results) . ' results');
                    return view('manga.jikan_results', compact('results'));
                } else {
                    Log::error('Invalid response format from Jikan API', ['body' => substr($body, 0, 500)]);
                    return back()->with('error', 'Invalid response format from MyAnimeList API');
                }
            } else {
                Log::error('MyAnimeList search failed: ' . $statusCode, ['body' => substr($body, 0, 500)]);
                return back()->with('error', 'Failed to search MyAnimeList. Status: ' . $statusCode);
            }
        } catch (\Exception $e) {
            Log::error('MyAnimeList search exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Import manga metadata from Jikan/MyAnimeList.
     */
    public function importFromJikan(Request $request)
    {
        $validated = $request->validate([
            'manga_id' => 'required|integer',
        ]);

        try {
            // Get manga details
            Log::info('Fetching manga details for ID: ' . $validated['manga_id']);
            $response = Http::get("https://api.jikan.moe/v4/manga/{$validated['manga_id']}");

            if (!$response->successful()) {
                Log::error('Failed to fetch manga details: ' . $response->status());
                return back()->with('error', 'Failed to fetch manga details. Status: ' . $response->status());
            }

            $mangaData = $response->json()['data'];
            Log::info('Successfully fetched manga: ' . $mangaData['title']);
            
            // Check if manga already exists
            $existingManga = Manga::where('title', $mangaData['title'])->first();
            if ($existingManga) {
                Log::info('Manga already exists in database: ' . $mangaData['title']);
                return redirect()->route('manga.show', $existingManga)
                    ->with('info', 'This manga already exists in your database.');
            }
            
            // Download cover image
            $coverPath = null;
            if (!empty($mangaData['images']['jpg']['large_image_url'])) {
                $coverUrl = $mangaData['images']['jpg']['large_image_url'];
                Log::info('Downloading cover image from: ' . $coverUrl);
                
                $coverImage = Http::get($coverUrl);
                if ($coverImage->successful()) {
                    $coverPath = "covers/" . Str::random(40) . ".jpg";
                    $result = Storage::disk('public')->put($coverPath, $coverImage->body());
                    Log::info('Cover image saved to: ' . $coverPath . ', Result: ' . ($result ? 'Success' : 'Failed'));
                } else {
                    Log::warning('Failed to download cover image: ' . $coverImage->status());
                }
            }
            
            // Create manga record
            Log::info('Creating manga record: ' . $mangaData['title']);
            $manga = Manga::create([
                'title' => $mangaData['title'],
                'description' => $mangaData['synopsis'] ?? null,
                'author' => $this->getAuthorsString($mangaData),
                'status' => $this->mapStatus($mangaData['status']),
                'cover_image' => $coverPath,
                'total_chapters' => $mangaData['chapters'] ?? 0,
            ]);
            
            // Now fetch chapters if available
            if (!empty($mangaData['chapters']) && $mangaData['chapters'] > 0) {
                $totalChapters = $mangaData['chapters'];
                Log::info("Creating {$totalChapters} chapter placeholders");
                
                // Create placeholder chapters based on the total count
                for ($i = 1; $i <= $totalChapters; $i++) {
                    $manga->chapters()->create([
                        'title' => "Chapter {$i}",
                        'chapter_number' => $i,
                    ]);
                    
                    // Add a small delay every 20 chapters to avoid database overload
                    if ($i % 20 == 0) {
                        usleep(100000); // 0.1 seconds
                    }
                }
                
                $manga->total_chapters = $totalChapters;
                $manga->save();
                
                Log::info("Created {$totalChapters} chapter placeholders successfully");
            } else {
                Log::info("No chapter information available for this manga");
            }
            
            return redirect()->route('manga.show', $manga)
                ->with('success', 'Manga metadata imported successfully! ' . 
                    ($manga->total_chapters > 0 ? "{$manga->total_chapters} chapter placeholders were created." : '') . 
                    ' You can now add chapter content manually.');
                
        } catch (\Exception $e) {
            Log::error('Exception during import: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
    
    /**
     * Extract authors from manga data.
     */
    private function getAuthorsString($mangaData)
    {
        $authors = [];
        
        if (!empty($mangaData['authors'])) {
            foreach ($mangaData['authors'] as $author) {
                $authors[] = $author['name'];
            }
        }
        
        return implode(', ', $authors);
    }
    
    /**
     * Map MyAnimeList status to our application status.
     */
    private function mapStatus($status)
    {
        return match ($status) {
            'Finished' => 'completed',
            'Publishing' => 'ongoing',
            'On Hiatus' => 'hiatus',
            default => 'ongoing',
        };
    }
}
