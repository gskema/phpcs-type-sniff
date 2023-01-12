<?php

namespace Gskema\TypeSniff\Core\Type\Common;

use Gskema\TypeSniff\Core\Type\TypeInterface;

/**
 * @see https://php.watch/versions/8.1/intersection-types
 */
class IntersectionType implements TypeInterface
{
    public function __construct(
        /** @var TypeInterface[] */
        protected array $types = [],
    ) {
    }

    public function isValid(): bool
    {
        foreach ($this->types as $type) {
            if (!is_a($type, FqcnType::class)) {
                return false;
            }
        }
        return true;
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

        return implode('&', $rawTypes);
    }
}
