<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\Type\TypeInterface;

class ConstElement implements CodeElementInterface
{
    public function __construct(
        protected int $line,
        protected DocBlock $docBlock,
        protected string $namespace,
        protected string $name,
        protected ?TypeInterface $valueType,
        /** @var string[] */
        protected array $attributeNames = [],
    ) {
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
