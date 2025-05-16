<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Manga;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Manga $manga, Chapter $chapter)
    {
        $pages = $chapter->pages()->orderBy('page_number')->get();
        return view('pages.index', compact('manga', 'chapter', 'pages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Manga $manga, Chapter $chapter)
    {
        return view('pages.create', compact('manga', 'chapter'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Manga $manga, Chapter $chapter)
    {
        $validated = $request->validate([
            'page_number' => 'required|integer|min:1',
            'image' => 'required|image|max:5120',
        ]);

        $path = $request->file('image')->store("chapters/{$manga->id}/{$chapter->id}", 'public');
        
        $page = $chapter->pages()->create([
            'page_number' => $validated['page_number'],
            'image_path' => $path,
        ]);

        return redirect()->route('manga.chapters.pages.index', [$manga, $chapter])
            ->with('success', 'Page added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Manga $manga, Chapter $chapter, Page $page)
    {
        return view('pages.show', compact('manga', 'chapter', 'page'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Manga $manga, Chapter $chapter, Page $page)
    {
        return view('pages.edit', compact('manga', 'chapter', 'page'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Manga $manga, Chapter $chapter, Page $page)
    {
        $validated = $request->validate([
            'page_number' => 'required|integer|min:1',
            'image' => 'nullable|image|max:5120',
        ]);

        $page->page_number = $validated['page_number'];

        if ($request->hasFile('image')) {
            // Delete old image
            Storage::disk('public')->delete($page->image_path);
            
            // Store new image
            $path = $request->file('image')->store("chapters/{$manga->id}/{$chapter->id}", 'public');
            $page->image_path = $path;
        }

        $page->save();

        return redirect()->route('manga.chapters.pages.index', [$manga, $chapter])
            ->with('success', 'Page updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Manga $manga, Chapter $chapter, Page $page)
    {
        // Delete image file
        Storage::disk('public')->delete($page->image_path);
        
        $page->delete();

        return redirect()->route('manga.chapters.pages.index', [$manga, $chapter])
            ->with('success', 'Page deleted successfully.');
    }
}
