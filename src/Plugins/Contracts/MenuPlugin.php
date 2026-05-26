<?php

namespace YellowThree\Voyager\Plugins\Contracts;

interface MenuPlugin
{
    /**
     * Return an array of menu items.
     *
     * @return array{title: string, route: string, icon?: string}[]
     */
    public function menuItems(): array;
}
