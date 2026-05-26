<?php

namespace YellowThree\VoyagerBlog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['parent_id', 'order', 'name', 'slug'];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
