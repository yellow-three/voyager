<?php

namespace YellowThree\VoyagerMenu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = ['name'];

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('order', 'ASC');
    }
}
