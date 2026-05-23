<?php

namespace YellowThree\Voyager\Database\Types\Postgresql;

use YellowThree\Voyager\Database\Types\Common\CharType;

class CharacterType extends CharType
{
    public const NAME = 'character';
    public const DBTYPE = 'bpchar';
}
