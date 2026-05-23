<?php

namespace YellowThree\Voyager\Plugins\Contracts;

use Illuminate\Database\Eloquent\Model;

interface AuthorizationPlugin
{
    public function authorize(string $ability, mixed $arguments = []): bool;
    public function getPermissionsForUser(Model $user): array;
}
