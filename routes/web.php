<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MangaController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\MangaImportController;
use App\Http\Controllers\MangaSourceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // User routes
    Route::get('/user/dashboard', [App\Http\Controllers\UserController::class, 'dashboard'])->name('user.dashboard');
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Manga import routes
    Route::get('/manga/import', [App\Http\Controllers\MangaImportController::class, 'showImportForm'])->name('manga.import.form');
    Route::post('/manga/search', [App\Http\Controllers\MangaImportController::class, 'searchMangaDex'])->name('manga.search');
    Route::post('/manga/import', [App\Http\Controllers\MangaImportController::class, 'importFromMangaDex'])->name('manga.import');
    Route::get('/manga/manual-import', [App\Http\Controllers\MangaImportController::class, 'showManualImportForm'])->name('manga.manual.import');
    Route::post('/manga/manual-import', [App\Http\Controllers\MangaImportController::class, 'processManualImport'])->name('manga.manual.process');
    Route::get('/manga/test-api', [App\Http\Controllers\MangaImportController::class, 'testMangaDexApi'])->name('manga.test.api');
    
    // Jikan API import routes
    Route::get('/manga/jikan-import', [App\Http\Controllers\JikanImportController::class, 'showImportForm'])->name('manga.jikan.form');
    Route::post('/manga/jikan-search', [App\Http\Controllers\JikanImportController::class, 'searchJikan'])->name('manga.jikan.search');
    Route::post('/manga/jikan-import', [App\Http\Controllers\JikanImportController::class, 'importFromJikan'])->name('manga.jikan.import');
    
    // MangaSee routes
    Route::get('/sources/mangasee', [MangaSourceController::class, 'showMangaSeeSearchForm'])->name('manga.sources.mangasee.search.form');
    Route::post('/sources/mangasee/search', [MangaSourceController::class, 'searchMangaSee'])->name('manga.sources.mangasee.search');
    Route::post('/sources/mangasee/import', [MangaSourceController::class, 'importFromMangaSee'])->name('manga.sources.mangasee.import');
    
    // MangaLife routes
    Route::get('/sources/mangalife', [MangaSourceController::class, 'showMangaLifeSearchForm'])->name('manga.sources.mangalife.search.form');
    Route::post('/sources/mangalife/search', [MangaSourceController::class, 'searchMangaLife'])->name('manga.sources.mangalife.search');
    Route::post('/sources/mangalife/import', [MangaSourceController::class, 'importFromMangaLife'])->name('manga.sources.mangalife.import');
    
    // Manga routes with admin middleware
    Route::middleware(['auth'])->group(function () {
        // Manga resource routes
        Route::resource('manga', MangaController::class);
        
        // MyAnimeList cover image routes
        Route::post('/manga/search-cover', [MangaController::class, 'searchCover'])->name('manga.search.cover');
        Route::post('/manga/{manga}/fetch-cover', [MangaController::class, 'fetchCover'])->name('manga.fetch.cover');
        Route::post('/manga/{manga}/fetch-chapters', [MangaController::class, 'fetchChapters'])->name('manga.fetch.chapters');
        
        // Nested routes for chapters
        Route::resource('manga.chapters', ChapterController::class);
        
        // Nested routes for pages
        Route::resource('manga.chapters.pages', PageController::class);
    });
});

// Public manga routes (no auth required)
Route::get('/manga', [MangaController::class, 'index'])->name('manga.index');
Route::get('/manga/{manga}', [MangaController::class, 'show'])->name('manga.show');
Route::get('/manga/{manga}/chapters/{chapter}', [ChapterController::class, 'show'])->name('manga.chapters.show');

require __DIR__.'/auth.php';
