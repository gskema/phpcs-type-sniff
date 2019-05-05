<?php

namespace Gskema\TypeSniff\Core\Type\Declaration;

use Gskema\TypeSniff\Core\Type\TypeInterface;

class NullableType implements TypeInterface
{
    /** @var TypeInterface */
    protected $type;

    public function __construct(TypeInterface $type)
    {
        $this->type = $type;
    }

    public function getType(): TypeInterface
    {
        return $this->type;
    }

    public function containsType(string $typeClassName): bool
    {
        return is_a($this->type, $typeClassName);
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return '?'.$this->type->toString();
    }

    public function toDocString(): string
    {
        return $this->type->toString().'|null';
    }
}
