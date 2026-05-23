<?php

namespace YellowThree\Voyager\Database\Types\Postgresql;

use YellowThree\Voyager\Database\Types\Common\DoubleType;

class DoublePrecisionType extends DoubleType
{
    public const NAME = 'double precision';
    public const DBTYPE = 'float8';
}
