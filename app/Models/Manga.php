<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Manga extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'cover_image',
        'author',
        'status',
        'total_chapters',
        'published_from',
        'published_to',
    ];
    
    protected $casts = [
        'published_from' => 'date',
        'published_to' => 'date',
    ];

    /**
     * Get the chapters for the manga.
     */
    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class);
    }
    
    /**
     * Get the cover image URL.
     * 
     * @return string|null
     */
    public function getCoverImageUrlAttribute()
    {
        if (!$this->cover_image) {
            return null;
        }
        
        // Check if the cover image is a full URL (external)
        if (filter_var($this->cover_image, FILTER_VALIDATE_URL)) {
            return $this->cover_image;
        }
        
        // Otherwise, it's a local file
        return asset('storage/' . $this->cover_image);
    }
}
