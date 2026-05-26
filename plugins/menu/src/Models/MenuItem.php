<?php

namespace YellowThree\VoyagerMenu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    protected $fillable = [
        'menu_id', 'title', 'url', 'target', 'icon_class', 'color', 'parent_id', 'order', 'route', 'parameters'
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('order', 'ASC');
    }
}
