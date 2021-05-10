<?php

namespace Gskema\TypeSniff\Core\Func;

use Gskema\TypeSniff\Core\Type\TypeInterface;

/**
 * @see FunctionParamTest
 */
class FunctionParam
{
    protected int $line;

    protected string $name;

    protected TypeInterface $type;

    protected ?TypeInterface $valueType;

    /** @var string[] */
    protected array $attributeNames = [];

    /**
     * @param int                $line
     * @param string             $name
     * @param TypeInterface      $declarationType
     * @param TypeInterface|null $valueType
     * @param string[]           $attributeNames
     */
    public function __construct(
        int $line,
        string $name,
        TypeInterface $declarationType,
        ?TypeInterface $valueType,
        array $attributeNames
    ) {
        $this->line = $line;
        $this->name = $name;
        $this->type = $declarationType;
        $this->valueType = $valueType;
        $this->attributeNames = $attributeNames;
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
