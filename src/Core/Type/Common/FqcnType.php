<?php

namespace Gskema\TypeSniff\Core\Type\Common;

use Gskema\TypeSniff\Core\Type\TypeInterface;

class FqcnType implements TypeInterface
{
    /** @var string */
    protected $fqcn;

    public function __construct(string $fqcn)
    {
        $this->fqcn = $fqcn;
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return $this->fqcn;
    }
}
