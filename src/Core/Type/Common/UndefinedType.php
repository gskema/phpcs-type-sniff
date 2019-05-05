<?php

namespace Gskema\TypeSniff\Core\Type\Common;

use Gskema\TypeSniff\Core\Type\TypeInterface;

class UndefinedType implements TypeInterface
{
    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return '';
    }
}
