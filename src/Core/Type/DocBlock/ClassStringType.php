<?php

namespace Gskema\TypeSniff\Core\Type\DocBlock;

use Gskema\TypeSniff\Core\Type\TypeInterface;

class ClassStringType implements TypeInterface
{
    public function toString(): string
    {
        return 'class-string';
    }
}
