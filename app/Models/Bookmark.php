<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Bookmark extends Model
{
    //
    use HasFactory;

    protected $fillable = ['folder_id', 'url', 'title', 'meta_description', 'keywords'];

    protected $casts = [
        'keywords' => 'array',
    ];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    // Bookmarks not yet confirmed into a folder — the "inbox" state
    public function scopeUnfiled($query)
    {
        return $query->whereNull('folder_id');
    }
}