<?php

namespace Gskema\TypeSniff\Core\Type\Common;

use Gskema\TypeSniff\Core\Type\TypeInterface;

class FloatType implements TypeInterface
{
    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return 'float';
    }
}
