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
    ];

    /**
     * Get the chapters for the manga.
     */
    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class);
    }
}
