<?php

namespace YellowThree\VoyagerBlog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    protected $fillable = [
        'author_id', 'category_id', 'title', 'seo_title', 'excerpt', 'body', 'image', 'slug', 'meta_description', 'meta_keywords', 'status', 'featured'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
