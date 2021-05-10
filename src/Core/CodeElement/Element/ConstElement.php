<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\Type\TypeInterface;

class ConstElement implements CodeElementInterface
{
    protected int $line;

    protected DocBlock $docBlock;

    protected string $namespace;

    protected string $name;

    protected ?TypeInterface $valueType;

    /** @var string[] */
    protected array $attributeNames = [];

    /**
     * @param int                $line
     * @param DocBlock           $docBlock
     * @param string             $namespace
     * @param string             $name
     * @param TypeInterface|null $valueType
     * @param string[]           $attributeNames
     */
    public function __construct(
        int $line,
        DocBlock $docBlock,
        string $namespace,
        string $name,
        ?TypeInterface $valueType,
        array $attributeNames
    ) {
        $this->line = $line;
        $this->docBlock = $docBlock;
        $this->namespace = $namespace;
        $this->name = $name;
        $this->valueType = $valueType;
        $this->attributeNames = $attributeNames;
    }

    /**
     * @inheritDoc
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * @inheritDoc
     */
    public function getDocBlock(): DocBlock
    {
        return $this->docBlock;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValueType(): ?TypeInterface
    {
        return $this->valueType;
    }

    /**
     * @inheritDoc
     */
    public function getAttributeNames(): array
    {
        return $this->attributeNames;
    }
}
