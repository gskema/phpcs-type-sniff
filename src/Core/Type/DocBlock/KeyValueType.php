<?php

namespace Gskema\TypeSniff\Core\Type\DocBlock;

use Gskema\TypeSniff\Core\Type\TypeInterface;

/**
 * E.g. Generator<int, string>, iterable<class-string>, etc.
 * Key value type is not stored - not necessary.
 *
 * Arrays use TypedArrayType.
 */
class KeyValueType implements TypeInterface
{
    public function __construct(
        protected TypeInterface $type
    ) {
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
        return $this->type->toString() . '<?>';
    }
}
