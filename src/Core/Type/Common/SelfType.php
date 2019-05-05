<?php

namespace Gskema\TypeSniff\Core\Type\Common;

use Gskema\TypeSniff\Core\Type\TypeInterface;

class SelfType implements TypeInterface
{
    /** @var string */
    protected $fqcn;

    public function __construct()
    {
        $this->fqcn = '';
    }

    public function getFqcn(): string
    {
        return $this->fqcn;
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return 'self';
    }
}
