<?php

namespace App\Http\Controllers;

use App\Services\MangaSeeService;
use App\Services\MangaLifeService;
use Illuminate\Http\Request;

class MangaSourceController extends Controller
{
    protected $mangaSeeService;
    protected $mangaLifeService;

    public function __construct(MangaSeeService $mangaSeeService, MangaLifeService $mangaLifeService)
    {
        $this->mangaSeeService = $mangaSeeService;
        $this->mangaLifeService = $mangaLifeService;
    }

    /**
     * Show search form for MangaSee
     */
    public function showMangaSeeSearchForm()
    {
        return view('manga.sources.mangasee.search');
    }

    /**
     * Search MangaSee
     */
    public function searchMangaSee(Request $request)
    {
        $validated = $request->validate([
            'query' => 'required|string|min:3|max:100',
        ]);

        $results = $this->mangaSeeService->search($validated['query']);

        return view('manga.sources.mangasee.results', compact('results'));
    }

    /**
     * Import manga from MangaSee
     */
    public function importFromMangaSee(Request $request)
    {
        $validated = $request->validate([
            'manga_slug' => 'required|string',
        ]);

        $result = $this->mangaSeeService->importManga($validated['manga_slug']);

        if ($result['success']) {
            return redirect()->route('manga.show', $result['manga'])
                ->with('success', $result['message']);
        } else {
            if (isset($result['manga'])) {
                return redirect()->route('manga.show', $result['manga'])
                    ->with('info', $result['message']);
            }
            return back()->with('error', $result['message']);
        }
    }

    /**
     * Show search form for MangaLife
     */
    public function showMangaLifeSearchForm()
    {
        return view('manga.sources.mangalife.search');
    }

    /**
     * Search MangaLife
     */
    public function searchMangaLife(Request $request)
    {
        $validated = $request->validate([
            'query' => 'required|string|min:3|max:100',
        ]);

        $results = $this->mangaLifeService->search($validated['query']);

        return view('manga.sources.mangalife.results', compact('results'));
    }

    /**
     * Import manga from MangaLife
     */
    public function importFromMangaLife(Request $request)
    {
        $validated = $request->validate([
            'manga_slug' => 'required|string',
        ]);

        $result = $this->mangaLifeService->importManga($validated['manga_slug']);

        if ($result['success']) {
            return redirect()->route('manga.show', $result['manga'])
                ->with('success', $result['message']);
        } else {
            if (isset($result['manga'])) {
                return redirect()->route('manga.show', $result['manga'])
                    ->with('info', $result['message']);
            }
            return back()->with('error', $result['message']);
        }
    }
}
