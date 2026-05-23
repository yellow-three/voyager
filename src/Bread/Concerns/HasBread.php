<?php

namespace YellowThree\Voyager\Bread\Concerns;

use YellowThree\Voyager\Bread\Bread;
use YellowThree\Voyager\Bread\BreadManager;

trait HasBread
{
    /**
     * Get BREAD configuration mapping for this model.
     *
     * @return Bread|null
     */
    public function getBreadAttribute(): ?Bread
    {
        $manager = app(BreadManager::class);
        $modelClass = get_class($this);

        return $manager->all()->first(fn($b) => $b->model_name === $modelClass);
    }
}
