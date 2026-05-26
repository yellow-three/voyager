<?php

namespace YellowThree\Voyager\Database\Types\Mysql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use YellowThree\Voyager\Database\Types\Type;

class LongBlobType extends Type
{
    public const NAME = 'longblob';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform)
    {
        return 'longblob';
    }
}
