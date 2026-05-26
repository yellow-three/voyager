<?php

namespace YellowThree\Voyager\Plugins\Contracts;

use Illuminate\Support\Collection;

interface FilterPlugin
{
    public function filterLayouts(mixed $bread, string $action, Collection $layouts): Collection;
    public function filterMenuItems(Collection $items): Collection;
    public function filterMedia(string $path, Collection $files): Collection;
}
