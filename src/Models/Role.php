<?php

namespace YellowThree\Voyager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use YellowThree\Voyager\Facades\Voyager;
use YellowThree\Voyager\Tests\Database\Factories\RoleFactory;

class Role extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function users()
    {
        $userModel = Voyager::modelClass('User');

        return $this->belongsToMany($userModel, 'user_roles')
                    ->select(app($userModel)->getTable().'.*')
                    ->union($this->hasMany($userModel))->getQuery();
    }

    public function permissions()
    {
        return $this->belongsToMany(Voyager::modelClass('Permission'));
    }

    protected static function newFactory()
    {
        return RoleFactory::new();
    }
}
