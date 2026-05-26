<?php

namespace YellowThree\Voyager\Plugins\Contracts;

interface WidgetPlugin
{
    /**
     * Return an array of dashboard widgets.
     *
     * @return array{view: string, data: array, width?: string}[]
     */
    public function widgets(): array;
}
