<?php

namespace Gskema\TypeSniff\Core\Type\DocBlock;

use Gskema\TypeSniff\Core\Type\TypeInterface;

class CompoundType implements TypeInterface
{
    /** @var TypeInterface[] */
    protected $types = [];

    /**
     * @param TypeInterface[] $types
     */
    public function __construct(array $types)
    {
        $this->types = $types;
    }

    /**
     * @return TypeInterface[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    public function getCount(): int
    {
        return count($this->types);
    }

    public function containsType(string $typeClassName): bool
    {
        foreach ($this->types as $type) {
            if (is_a($type, $typeClassName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $typeClassName
     *
     * @return TypeInterface[]
     */
    public function getType(string $typeClassName): array
    {
        $types = [];
        foreach ($this->types as $type) {
            if (is_a($type, $typeClassName)) {
                $types[] = $type;
            }
        }

        return $types;
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        $rawTypes = [];
        foreach ($this->types as $type) {
            $rawTypes[] = $type->toString();
        }
        sort($rawTypes);
        // @TODO Ensure null is last?

        return implode('|', $rawTypes);
    }
}
