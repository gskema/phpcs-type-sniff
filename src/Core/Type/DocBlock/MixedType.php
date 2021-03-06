<?php

namespace Gskema\TypeSniff\Core\Type\DocBlock;

use Gskema\TypeSniff\Core\Type\TypeInterface;

class MixedType implements TypeInterface
{
    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return 'mixed';
    }
}
