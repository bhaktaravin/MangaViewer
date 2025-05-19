<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MyAnimeListService
{
    /**
     * Search for manga on MyAnimeList
     *
     * @param string $title
     * @return array|null
     */
    public function searchManga(string $title)
    {
        try {
            $response = Http::get('https://api.jikan.moe/v4/manga', [
                'q' => $title,
                'limit' => 5
            ]);
            
            if ($response->successful()) {
                return $response->json()['data'] ?? null;
            }
            
            Log::warning('MyAnimeList API request failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            return null;
        } catch (\Exception $e) {
            Log::error('Error searching MyAnimeList', [
                'error' => $e->getMessage(),
                'title' => $title
            ]);
            
            return null;
        }
    }
    
    /**
     * Get cover image URL from MyAnimeList for a manga title
     *
     * @param string $title
     * @return string|null
     */
    public function getCoverImageUrl(string $title)
    {
        $results = $this->searchManga($title);
        
        if (!$results || empty($results)) {
            return null;
        }
        
        // Return the image URL from the first result
        return $results[0]['images']['jpg']['large_image_url'] ?? null;
    }
    
    /**
     * Download a cover image from MyAnimeList and store it locally
     *
     * @param string $title
     * @return string|null Path to the stored image
     */
    public function downloadCoverImage(string $title)
    {
        $imageUrl = $this->getCoverImageUrl($title);
        
        if (!$imageUrl) {
            return null;
        }
        
        try {
            $response = Http::get($imageUrl);
            
            if ($response->successful()) {
                $filename = 'covers/' . Str::random(40) . '.jpg';
                Storage::disk('public')->put($filename, $response->body());
                return $filename;
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('Error downloading cover image', [
                'error' => $e->getMessage(),
                'url' => $imageUrl
            ]);
            
            return null;
        }
    }
}
