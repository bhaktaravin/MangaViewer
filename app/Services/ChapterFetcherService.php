<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Manga;
use App\Models\Chapter;

class ChapterFetcherService
{
    /**
     * Fetch chapters from MyAnimeList (via Jikan API)
     */
    public function fetchFromMyAnimeList(Manga $manga)
    {
        try {
            // First, search for the manga on MyAnimeList
            $searchResponse = Http::get('https://api.jikan.moe/v4/manga', [
                'q' => $manga->title,
                'limit' => 1
            ]);
            
            if (!$searchResponse->successful()) {
                Log::error('Failed to search manga on MyAnimeList', [
                    'manga_id' => $manga->id,
                    'title' => $manga->title,
                    'status' => $searchResponse->status()
                ]);
                return false;
            }
            
            $searchData = $searchResponse->json();
            $results = $searchData['data'] ?? [];
            
            if (empty($results)) {
                Log::error('No results found on MyAnimeList', [
                    'manga_id' => $manga->id,
                    'title' => $manga->title
                ]);
                return false;
            }
            
            $malId = $results[0]['mal_id'];
            $mangaDetails = $results[0];
            
            // Update manga information
            $updateData = [];
            
            // Update status if available
            if (isset($mangaDetails['status'])) {
                $status = strtolower($mangaDetails['status']);
                if ($status === 'finished') {
                    $updateData['status'] = 'completed';
                } elseif ($status === 'publishing') {
                    $updateData['status'] = 'ongoing';
                } elseif ($status === 'on hiatus') {
                    $updateData['status'] = 'hiatus';
                }
            }
            
            // Update description if available
            if (isset($mangaDetails['synopsis']) && !empty($mangaDetails['synopsis'])) {
                $updateData['description'] = $mangaDetails['synopsis'];
            }
            
            // Update author if available
            if (isset($mangaDetails['authors']) && !empty($mangaDetails['authors'])) {
                $authors = [];
                foreach ($mangaDetails['authors'] as $author) {
                    $authors[] = $author['name'];
                }
                $updateData['author'] = implode(', ', $authors);
            }
            
            // Update publication dates if available
            if (isset($mangaDetails['published']['prop']['from']) && !empty($mangaDetails['published']['prop']['from'])) {
                $fromYear = $mangaDetails['published']['prop']['from']['year'] ?? null;
                $fromMonth = $mangaDetails['published']['prop']['from']['month'] ?? null;
                $fromDay = $mangaDetails['published']['prop']['from']['day'] ?? null;
                
                if ($fromYear && $fromMonth && $fromDay) {
                    $updateData['published_from'] = "{$fromYear}-{$fromMonth}-{$fromDay}";
                }
            }
            
            if (isset($mangaDetails['published']['prop']['to']) && !empty($mangaDetails['published']['prop']['to'])) {
                $toYear = $mangaDetails['published']['prop']['to']['year'] ?? null;
                $toMonth = $mangaDetails['published']['prop']['to']['month'] ?? null;
                $toDay = $mangaDetails['published']['prop']['to']['day'] ?? null;
                
                if ($toYear && $toMonth && $toDay) {
                    $updateData['published_to'] = "{$toYear}-{$toMonth}-{$toDay}";
                }
            }
            
            // Apply updates if any
            if (!empty($updateData)) {
                $manga->update($updateData);
            }
            
            // Get manga details including chapter count
            $detailsResponse = Http::get("https://api.jikan.moe/v4/manga/{$malId}/full");
            
            if (!$detailsResponse->successful()) {
                Log::error('Failed to fetch manga details from MyAnimeList', [
                    'manga_id' => $manga->id,
                    'mal_id' => $malId,
                    'status' => $detailsResponse->status()
                ]);
                return false;
            }
            
            $detailsData = $detailsResponse->json();
            $mangaDetails = $detailsData['data'] ?? null;
            
            if (!$mangaDetails) {
                Log::error('No manga details found on MyAnimeList', [
                    'manga_id' => $manga->id,
                    'mal_id' => $malId
                ]);
                return false;
            }
            
            $totalChapters = $mangaDetails['chapters'] ?? 0;
            
            if ($totalChapters <= 0) {
                Log::warning('No chapters information available on MyAnimeList', [
                    'manga_id' => $manga->id,
                    'mal_id' => $malId
                ]);
                return 0;
            }
            
            // Create chapters based on the total count
            $count = 0;
            for ($i = 1; $i <= $totalChapters; $i++) {
                // Check if chapter already exists
                $existingChapter = Chapter::where('manga_id', $manga->id)
                    ->where('chapter_number', $i)
                    ->first();
                
                if ($existingChapter) continue;
                
                // Create new chapter
                $chapter = new Chapter();
                $chapter->manga_id = $manga->id;
                $chapter->chapter_number = $i;
                $chapter->title = "Chapter {$i}";
                $chapter->content = "Content for chapter {$i} of {$manga->title}";
                $chapter->save();
                
                $count++;
            }
            
            // Update manga total chapters
            $manga->total_chapters = Chapter::where('manga_id', $manga->id)->count();
            $manga->save();
            
            return $count;
        } catch (\Exception $e) {
            Log::error('Error fetching chapters from MyAnimeList', [
                'manga_id' => $manga->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
