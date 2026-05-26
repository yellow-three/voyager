<?php

namespace YellowThree\Voyager\Database\Types\Mysql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use YellowThree\Voyager\Database\Types\Type;

class MediumBlobType extends Type
{
    public const NAME = 'mediumblob';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform)
    {
        return 'mediumblob';
    }
}
