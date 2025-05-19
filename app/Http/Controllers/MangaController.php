<?php

namespace App\Http\Controllers;

use App\Models\Manga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\MyAnimeListService;

class MangaController extends Controller
{
    protected $myAnimeListService;
    
    /**
     * Create a new controller instance.
     *
     * @param MyAnimeListService $myAnimeListService
     * @return void
     */
    public function __construct(MyAnimeListService $myAnimeListService)
    {
        $this->myAnimeListService = $myAnimeListService;
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $mangas = Manga::orderBy('title')->paginate(12);
        return view('manga.index', compact('mangas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('manga.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'author' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:ongoing,completed,hiatus',
            'cover_image' => 'nullable|image|max:2048',
            'cover_image_url' => 'nullable|url',
        ]);

        // Handle file upload
        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('covers', 'public');
            $validated['cover_image'] = $path;
        }
        // Handle external URL
        elseif ($request->filled('cover_image_url')) {
            $validated['cover_image'] = $request->cover_image_url;
        }

        $manga = Manga::create($validated);

        return redirect()->route('manga.show', $manga)
            ->with('success', 'Manga created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Manga $manga)
    {
        $chapters = $manga->chapters()->orderBy('chapter_number')->get();
        return view('manga.show', compact('manga', 'chapters'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Manga $manga)
    {
        return view('manga.edit', compact('manga'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Manga $manga)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'author' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:ongoing,completed,hiatus',
            'cover_image' => 'nullable|image|max:2048',
            'cover_image_url' => 'nullable|url',
        ]);

        // Handle file upload
        if ($request->hasFile('cover_image')) {
            // Delete old cover image if it's a local file
            if ($manga->cover_image && !filter_var($manga->cover_image, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($manga->cover_image);
            }
            
            $path = $request->file('cover_image')->store('covers', 'public');
            $validated['cover_image'] = $path;
        }
        // Handle external URL
        elseif ($request->filled('cover_image_url')) {
            // Delete old cover image if it's a local file
            if ($manga->cover_image && !filter_var($manga->cover_image, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($manga->cover_image);
            }
            
            $validated['cover_image'] = $request->cover_image_url;
        }

        $manga->update($validated);

        return redirect()->route('manga.show', $manga)
            ->with('success', 'Manga updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Manga $manga)
    {
        // Delete cover image if exists
        if ($manga->cover_image) {
            Storage::disk('public')->delete($manga->cover_image);
        }
        
        $manga->delete();

        return redirect()->route('manga.index')
            ->with('success', 'Manga deleted successfully.');
    }
    
    /**
     * Search for a manga cover on MyAnimeList.
     */
    public function searchCover(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);
        
        $results = $this->myAnimeListService->searchManga($validated['title']);
        
        return response()->json([
            'results' => $results
        ]);
    }
    
    /**
     * Fetch a cover image from MyAnimeList.
     */
    public function fetchCover(Request $request, Manga $manga)
    {
        $coverPath = $this->myAnimeListService->downloadCoverImage($manga->title);
        
        if ($coverPath) {
            // Delete old cover image if it's a local file
            if ($manga->cover_image && !filter_var($manga->cover_image, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($manga->cover_image);
            }
            
            $manga->update(['cover_image' => $coverPath]);
            
            return redirect()->route('manga.show', $manga)
                ->with('success', 'Cover image updated successfully.');
        }
        
        return redirect()->route('manga.show', $manga)
            ->with('error', 'Failed to fetch cover image from MyAnimeList.');
    }
    
    /**
     * Fetch chapters from MyAnimeList
     */
    public function fetchChapters(Request $request, Manga $manga)
    {
        $chapterFetcher = new \App\Services\ChapterFetcherService();
        $count = $chapterFetcher->fetchFromMyAnimeList($manga);
        
        if ($count === false) {
            return redirect()->route('manga.show', $manga)
                ->with('error', 'Failed to fetch chapters from MyAnimeList.');
        }
        
        return redirect()->route('manga.show', $manga)
            ->with('success', "{$count} new chapters imported successfully.");
    }
}
