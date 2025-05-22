<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Manga;
use App\Models\Chapter;
use App\Models\Page;

class MangaImportController extends Controller
{
    /**
     * Display the import form.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('manga.import');
    }

    /**
     * Display the manual import form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showManualImportForm()
    {
        return view('manga.manual-import');
    }

    /**
     * Display the import form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showImportForm()
    {
        return view('manga.import-form');
    }

    /**
     * Process the manga import.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'files' => 'required',
            // Add more validation rules as needed
        ]);

        // Process the import logic here
        // This is a placeholder for your actual import implementation

        return redirect()->route('manga.index')
            ->with('success', 'Manga imported successfully.');
    }

    /**
     * Process manual manga import.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function processManualImport(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'files' => 'required|array',
            'files.*' => 'file|mimes:zip,rar|max:50000',
        ]);

        // Process the manual import logic here
        // This is a placeholder for your actual import implementation

        return redirect()->route('manga.index')
            ->with('success', 'Manga imported successfully.');
    }

    /**
     * Search manga from MangaDex API.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function searchMangaDex(Request $request)
    {
        $request->validate([
            'search' => 'required|string|min:3',
        ]);

        $searchTerm = $request->search;
        $results = [];

        try {
            // Log the search attempt
            \Log::info('Searching MangaDex for: ' . $searchTerm);

            // Build the URL with properly encoded parameters
            $baseUrl = "https://api.mangadex.org/manga";
            $url = $baseUrl . "?title=" . urlencode($searchTerm) .
                   "&limit=12" .
                   "&includes[]=cover_art" .
                   "&includes[]=author" .
                   "&contentRating[]=safe" .
                   "&contentRating[]=suggestive" .
                   "&order[relevance]=desc";

            \Log::info('Making request to URL: ' . $url);

            // Make the API request
            $client = new \GuzzleHttp\Client();
            $response = $client->request('GET', $url);

            // Log the response status
            \Log::info('MangaDex API response status: ' . $response->getStatusCode());

            if ($response->getStatusCode() == 200) {
                $data = json_decode($response->getBody(), true);

                // Log the response data structure
                \Log::info('MangaDex API response structure: ' . json_encode(array_keys($data)));

                // Process the results
                if (isset($data['data']) && is_array($data['data'])) {
                    \Log::info('Found ' . count($data['data']) . ' manga results');

                    foreach ($data['data'] as $manga) {
                        $title = '';
                        if (isset($manga['attributes']['title'])) {
                            if (isset($manga['attributes']['title']['en'])) {
                                $title = $manga['attributes']['title']['en'];
                            } elseif (isset($manga['attributes']['title']['ja-ro'])) {
                                $title = $manga['attributes']['title']['ja-ro'];
                            } elseif (!empty($manga['attributes']['title'])) {
                                $title = array_values($manga['attributes']['title'])[0];
                            }
                        }

                        $description = '';
                        if (isset($manga['attributes']['description'])) {
                            if (isset($manga['attributes']['description']['en'])) {
                                $description = $manga['attributes']['description']['en'];
                            } elseif (isset($manga['attributes']['description']['ja-ro'])) {
                                $description = $manga['attributes']['description']['ja-ro'];
                            } elseif (!empty($manga['attributes']['description'])) {
                                $description = array_values($manga['attributes']['description'])[0];
                            }
                        }

                        $mangaData = [
                            'id' => $manga['id'],
                            'title' => $title ?: 'Unknown Title',
                            'description' => $description,
                            'coverUrl' => 'https://uploads.mangadex.org/covers/' . $manga['id'] . '/0.jpg',
                            'author' => 'Unknown Author',
                        ];

                        // Extract cover image and author
                        if (isset($manga['relationships'])) {
                            foreach ($manga['relationships'] as $relationship) {
                                if ($relationship['type'] === 'cover_art' && isset($relationship['attributes']['fileName'])) {
                                    $mangaData['coverUrl'] = 'https://uploads.mangadex.org/covers/' . $manga['id'] . '/' . $relationship['attributes']['fileName'] . '.512.jpg';
                                }

                                if ($relationship['type'] === 'author' && isset($relationship['attributes']['name'])) {
                                    $mangaData['author'] = $relationship['attributes']['name'];
                                }
                            }
                        }

                        $results[] = $mangaData;
                    }
                }
            }
        } catch (\Exception $e) {
            // Log the error
            \Log::error('MangaDex API Error: ' . $e->getMessage());
            \Log::error('Exception trace: ' . $e->getTraceAsString());
        }

        // Log the final results count
        \Log::info('Returning ' . count($results) . ' manga results to view');

        return view('manga.search-results', [
            'results' => $results,
            'search' => $searchTerm,
        ]);
    }

    /**
     * Import manga from MangaDex.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function importFromMangaDex(Request $request)
    {
        $request->validate([
            'manga_id' => 'required|string',
        ]);

        $mangaId = $request->manga_id;
        \Log::info('Importing manga from MangaDex with ID: ' . $mangaId);

        try {
            // Fetch manga details from MangaDex API
            $client = new \GuzzleHttp\Client();
            $url = "https://api.mangadex.org/manga/{$mangaId}?includes[]=cover_art&includes[]=author";

            \Log::info('Making request to URL: ' . $url);
            $response = $client->request('GET', $url);

            if ($response->getStatusCode() == 200) {
                $data = json_decode($response->getBody(), true);
                \Log::debug('Full MangaDex API response: ' . json_encode($data));

                if (isset($data['data'])) {
                    $mangaData = $data['data'];

                    // Extract manga details
                    $title = '';
                    if (isset($mangaData['attributes']['title'])) {
                        if (isset($mangaData['attributes']['title']['en'])) {
                            $title = $mangaData['attributes']['title']['en'];
                        } elseif (isset($mangaData['attributes']['title']['ja-ro'])) {
                            $title = $mangaData['attributes']['title']['ja-ro'];
                        } elseif (!empty($mangaData['attributes']['title'])) {
                            $title = array_values($mangaData['attributes']['title'])[0];
                        }
                    }

                    $description = '';
                    if (isset($mangaData['attributes']['description'])) {
                        if (isset($mangaData['attributes']['description']['en'])) {
                            $description = $mangaData['attributes']['description']['en'];
                        } elseif (isset($mangaData['attributes']['description']['ja-ro'])) {
                            $description = $mangaData['attributes']['description']['ja-ro'];
                        } elseif (!empty($mangaData['attributes']['description'])) {
                            $description = array_values($mangaData['attributes']['description'])[0];
                        }
                    }

                    $status = 'ongoing';
                    if (isset($mangaData['attributes']['status'])) {
                        $status = $mangaData['attributes']['status'];
                    }

                    $coverImage = null;
                    $author = 'Unknown Author';

                    // Extract cover image and author
                    if (isset($mangaData['relationships'])) {
                        foreach ($mangaData['relationships'] as $relationship) {
                            if ($relationship['type'] === 'cover_art') {
                                \Log::debug('Found cover_art relationship: ' . json_encode($relationship));

                                if (isset($relationship['attributes']) && isset($relationship['attributes']['fileName'])) {
                                    $fileName = $relationship['attributes']['fileName'];
                                    $coverImage = "https://uploads.mangadex.org/covers/{$mangaId}/{$fileName}";
                                    \Log::info('Cover image URL: ' . $coverImage);
                                }
                            }

                            if ($relationship['type'] === 'author' && isset($relationship['attributes']['name'])) {
                                $author = $relationship['attributes']['name'];
                            }
                        }
                    }

                    // Save the manga to the database
                    \Log::info('Creating manga record with title: ' . $title);

                    $manga = \App\Models\Manga::create([
                        'title' => $title ?: 'Unknown Title',
                        'description' => $description,
                        'cover_image' => $coverImage,
                        'author' => $author,
                        'status' => $status,
                        'total_chapters' => 0, // Will be updated when chapters are imported
                    ]);

                    \Log::info('Manga created with ID: ' . $manga->id);

                    // Fetch and import chapters
                    $this->importChaptersForManga($manga, $mangaId);

                    return redirect()->route('manga.index')
                        ->with('success', 'Manga "' . $title . '" imported successfully from MangaDex.');
                } else {
                    \Log::error('Invalid manga data structure from MangaDex API');
                    return redirect()->route('manga.import.form')
                        ->with('error', 'Failed to import manga: Invalid data structure from MangaDex API');
                }
            } else {
                \Log::error('MangaDex API returned status code: ' . $response->getStatusCode());
                return redirect()->route('manga.import.form')
                    ->with('error', 'Failed to import manga: MangaDex API returned status code ' . $response->getStatusCode());
            }
        } catch (\Exception $e) {
            \Log::error('Error importing manga from MangaDex: ' . $e->getMessage());
            \Log::error('Exception trace: ' . $e->getTraceAsString());

            return redirect()->route('manga.import.form')
                ->with('error', 'Failed to import manga: ' . $e->getMessage());
        }
    }

    /**
     * Import chapters for a manga from MangaDex.
     *
     * @param  \App\Models\Manga  $manga
     * @param  string  $mangaId
     * @return void
     */
    protected function importChaptersForManga($manga, $mangaId)
    {
        try {
            \Log::info('Importing chapters for manga ID: ' . $mangaId);

            // Fetch chapters from MangaDex API
            $client = new \GuzzleHttp\Client();
            $url = "https://api.mangadex.org/manga/{$mangaId}/feed?translatedLanguage[]=en&limit=100&order[chapter]=asc";

            \Log::info('Making request to URL: ' . $url);
            $response = $client->request('GET', $url);

            if ($response->getStatusCode() == 200) {
                $data = json_decode($response->getBody(), true);

                if (isset($data['data']) && is_array($data['data'])) {
                    $chaptersCount = count($data['data']);
                    \Log::info('Found ' . $chaptersCount . ' chapters');

                    foreach ($data['data'] as $index => $chapterData) {
                        if (isset($chapterData['attributes']['chapter'])) {
                            $chapterNumber = $chapterData['attributes']['chapter'];
                            $chapterTitle = $chapterData['attributes']['title'] ?? 'Chapter ' . $chapterNumber;

                            \Log::info('Importing chapter ' . $chapterNumber . ': ' . $chapterTitle);

                            // Create chapter record
                            $chapter = $manga->chapters()->create([
                                'title' => $chapterTitle,
                                'chapter_number' => $chapterNumber,
                                'content' => null,
                                'file_path' => null,
                            ]);

                            \Log::info('Chapter created with ID: ' . $chapter->id);
                        }
                    }

                    // Update manga total chapters
                    $manga->total_chapters = $chaptersCount;
                    $manga->save();

                    \Log::info('Updated manga total chapters to: ' . $chaptersCount);
                } else {
                    \Log::error('No chapters found or invalid data structure');
                }
            } else {
                \Log::error('MangaDex API returned status code: ' . $response->getStatusCode() . ' when fetching chapters');
            }
        } catch (\Exception $e) {
            \Log::error('Error importing chapters: ' . $e->getMessage());
        }
    }

    /**
     * Test MangaDex API connection.
     *
     * @return \Illuminate\Http\Response
     */
    public function testMangaDexApi()
    {
        try {
            // Create a new Guzzle HTTP client with timeout settings
            $client = new \GuzzleHttp\Client([
                'timeout' => 10,
                'connect_timeout' => 5,
                'http_errors' => false, // Don't throw exceptions for HTTP errors
            ]);
            
            // Log the attempt
            \Illuminate\Support\Facades\Log::info('Testing MangaDex API connection');
            
            // Test connection to MangaDex API
            $response = $client->get('https://api.mangadex.org/manga', [
                'query' => [
                    'limit' => 1
                ]
            ]);
            
            // Get the response body
            $body = $response->getBody()->getContents();
            
            // Log the response status and body for debugging
            \Illuminate\Support\Facades\Log::info('MangaDex API Test Response', [
                'status' => $response->getStatusCode(),
                'body_preview' => substr($body, 0, 500)
            ]);
            
            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                // For web requests
                if (request()->expectsJson()) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'MangaDex API connection successful',
                        'data' => json_decode($body, true)
                    ]);
                }
                
                return back()->with('success', 'Successfully connected to MangaDex API!');
            } else {
                $errorMessage = 'Error connecting to MangaDex API. Status code: ' . $response->getStatusCode();
                
                // Log the error
                \Illuminate\Support\Facades\Log::error($errorMessage, [
                    'response' => $body
                ]);
                
                // For web requests
                if (request()->expectsJson()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $errorMessage
                    ]);
                }
                
                return back()->with('error', $errorMessage);
            }
        } catch (\Exception $e) {
            // Log the exception for debugging
            \Illuminate\Support\Facades\Log::error('MangaDex API Test Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $errorMessage = 'Error testing API connection: ' . $e->getMessage();
            
            // For web requests
            if (request()->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $errorMessage
                ]);
            }
            
            return back()->with('error', $errorMessage);
        }
    }
}
