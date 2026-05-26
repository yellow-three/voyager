<?php

namespace YellowThree\Voyager\Upgrade\Contracts;

use Illuminate\Console\Command;

interface StepContract
{
    public function getLabel(): string;

    public function execute(Command $command): int;
}
