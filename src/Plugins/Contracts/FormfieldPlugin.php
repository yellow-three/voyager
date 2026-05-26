<?php

namespace YellowThree\Voyager\Plugins\Contracts;

interface FormfieldPlugin
{
    /**
     * Return an array of FormField handler class names.
     *
     * @return string[]
     */
    public function formFields(): array;
}
