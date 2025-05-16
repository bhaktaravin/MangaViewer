<?php

namespace App\Http\Controllers;

use App\Models\Manga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MangaController extends Controller
{
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
        ]);

        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('covers', 'public');
            $validated['cover_image'] = $path;
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
        ]);

        if ($request->hasFile('cover_image')) {
            // Delete old cover image if exists
            if ($manga->cover_image) {
                Storage::disk('public')->delete($manga->cover_image);
            }
            
            $path = $request->file('cover_image')->store('covers', 'public');
            $validated['cover_image'] = $path;
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
}
