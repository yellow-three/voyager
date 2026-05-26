<?php

namespace YellowThree\Voyager\Database\Types\Mysql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use YellowThree\Voyager\Database\Types\Type;

class TinyBlobType extends Type
{
    public const NAME = 'tinyblob';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform)
    {
        return 'tinyblob';
    }
}
