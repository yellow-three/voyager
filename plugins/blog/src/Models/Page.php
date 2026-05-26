<?php

namespace YellowThree\VoyagerBlog\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'author_id', 'title', 'excerpt', 'body', 'image', 'slug', 'meta_description', 'meta_keywords', 'status'
    ];
}
