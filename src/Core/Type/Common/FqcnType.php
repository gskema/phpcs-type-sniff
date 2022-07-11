<?php

namespace Gskema\TypeSniff\Core\Type\Common;

use Gskema\TypeSniff\Core\Type\TypeInterface;

class FqcnType implements TypeInterface
{
    public function __construct(
        protected string $fqcn,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return $this->fqcn;
    }
}
