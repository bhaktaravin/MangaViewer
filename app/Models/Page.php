<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Page extends Model
{
    use HasFactory;
    
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'manga_db';

    protected $fillable = [
        'chapter_id',
        'page_number',
        'image_path',
    ];

    /**
     * Get the chapter that owns the page.
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }
}
