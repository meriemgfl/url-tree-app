<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Folder extends Model
{
    //
    use HasFactory;


protected $fillable = ['parent_id', 'name', 'keywords'];

    protected $casts = [
        'keywords' => 'array',
    ];

    // The folder directly above this one (null = root level)
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }
    // Direct child folders only, one level down
    public function children(): HasMany
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    // Bookmarks placed directly in this folder
    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

 // All root-level folders (no parent) — the top of the tree
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    // Recursively load this folder + all descendants + their bookmarks,
    // useful for rendering the whole tree in one query-efficient call
    public function loadTree(): self
    {
        return $this->load(['children.children', 'bookmarks']);
        // Note: for arbitrary depth, see the recursive helper below instead
    }

    // Walks down and returns a flat collection of every descendant folder
    // (useful for the similarity matcher later — "compare against all folders")
    public function descendants(): Collection
    {
        $all = collect();

        foreach ($this->children as $child) {
            $all->push($child);
            $all = $all->merge($child->descendants());
        }

        return $all;
    }

    // Builds the full breadcrumb path as a string, e.g. "Programming > PHP > Laravel"
    public function path(): string
    {
        $segments = [];
        $node = $this;

        while ($node !== null) {
            array_unshift($segments, $node->name);
            $node = $node->parent;
        }

        return implode(' > ', $segments);
    }
    
    //when a bookmark is confirmed into a folder, merge its keywords into that folder's set
    public function absorbKeywords(array $newKeywords, int $cap = 30): void
    {
    $existing = $this->keywords ?? [];
    $merged = array_values(array_unique(array_merge($existing, $newKeywords)));

    // Keep it bounded — otherwise every folder eventually contains every word
    $this->keywords = array_slice($merged, 0, $cap);
    $this->save();
    }
}

