<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Manga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChapterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Manga $manga)
    {
        $chapters = $manga->chapters()->orderBy('chapter_number')->paginate(20);
        return view('chapters.index', compact('manga', 'chapters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Manga $manga)
    {
        return view('chapters.create', compact('manga'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Manga $manga)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'chapter_number' => 'required|integer|min:0',
            'content' => 'nullable|string',
            'images.*' => 'nullable|image|max:5120',
        ]);

        $chapter = new Chapter([
            'title' => $validated['title'],
            'chapter_number' => $validated['chapter_number'],
            'content' => $validated['content'] ?? null,
        ]);

        $manga->chapters()->save($chapter);

        // Handle uploaded images
        if ($request->hasFile('images')) {
            $pageNumber = 1;
            foreach ($request->file('images') as $image) {
                $path = $image->store("chapters/{$manga->id}/{$chapter->id}", 'public');
                
                $chapter->pages()->create([
                    'page_number' => $pageNumber++,
                    'image_path' => $path,
                ]);
            }
        }

        // Update manga total chapters count
        $manga->total_chapters = $manga->chapters()->count();
        $manga->save();

        return redirect()->route('manga.chapters.show', [$manga, $chapter])
            ->with('success', 'Chapter created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Manga $manga, Chapter $chapter)
    {
        $pages = $chapter->pages;
        return view('chapters.show', compact('manga', 'chapter', 'pages'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Manga $manga, Chapter $chapter)
    {
        return view('chapters.edit', compact('manga', 'chapter'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Manga $manga, Chapter $chapter)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'chapter_number' => 'required|integer|min:0',
            'content' => 'nullable|string',
            'images.*' => 'nullable|image|max:5120',
        ]);

        $chapter->update([
            'title' => $validated['title'],
            'chapter_number' => $validated['chapter_number'],
            'content' => $validated['content'] ?? null,
        ]);

        // Handle uploaded images
        if ($request->hasFile('images')) {
            $lastPage = $chapter->pages()->max('page_number') ?? 0;
            $pageNumber = $lastPage + 1;
            
            foreach ($request->file('images') as $image) {
                $path = $image->store("chapters/{$manga->id}/{$chapter->id}", 'public');
                
                $chapter->pages()->create([
                    'page_number' => $pageNumber++,
                    'image_path' => $path,
                ]);
            }
        }

        return redirect()->route('manga.chapters.show', [$manga, $chapter])
            ->with('success', 'Chapter updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Manga $manga, Chapter $chapter)
    {
        // Delete all chapter pages and their images
        foreach ($chapter->pages as $page) {
            Storage::disk('public')->delete($page->image_path);
        }
        
        $chapter->delete();
        
        // Update manga total chapters count
        $manga->total_chapters = $manga->chapters()->count();
        $manga->save();

        return redirect()->route('manga.show', $manga)
            ->with('success', 'Chapter deleted successfully.');
    }
}
