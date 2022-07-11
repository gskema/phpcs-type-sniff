<?php

namespace Gskema\TypeSniff\Core\Func;

use Gskema\TypeSniff\Core\Type\TypeInterface;

/**
 * @see FunctionParamTest
 */
class FunctionParam
{
    public function __construct(
        protected int $line,
        protected string $name,
        protected TypeInterface $type,
        protected ?TypeInterface $valueType,
        /** @var string[] */
        protected array $attributeNames = [],
    ) {
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): TypeInterface
    {
        return $this->type;
    }

    public function getValueType(): ?TypeInterface
    {
        return $this->valueType;
    }

    /**
     * @return string[]
     */
    public function getAttributeNames(): array
    {
        return $this->attributeNames;
    }

    public function hasAttribute(string $attributeName): bool
    {
        return in_array($attributeName, $this->attributeNames);
    }
}
