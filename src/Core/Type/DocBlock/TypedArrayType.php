<?php

namespace Gskema\TypeSniff\Core\Type\DocBlock;

use Gskema\TypeSniff\Core\Type\TypeInterface;

class TypedArrayType implements TypeInterface
{
    protected TypeInterface $type;

    protected int $depth;

    public function __construct(TypeInterface $type, int $depth)
    {
        $this->type = $type;
        $this->depth = $depth;
    }

    public function getType(): TypeInterface
    {
        return $this->type;
    }

    public function getDepth(): int
    {
        return $this->depth;
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        $innerType = $this->type->toString();
        if ($this->type instanceof CompoundType) {
            $innerType = sprintf('(%s)', $innerType);
        }

        return $innerType . str_repeat('[]', $this->depth);
    }
}
