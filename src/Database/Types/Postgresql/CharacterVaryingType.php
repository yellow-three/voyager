<?php

namespace YellowThree\Voyager\Database\Types\Postgresql;

use YellowThree\Voyager\Database\Types\Common\VarCharType;

class CharacterVaryingType extends VarCharType
{
    public const NAME = 'character varying';
    public const DBTYPE = 'varchar';
}
