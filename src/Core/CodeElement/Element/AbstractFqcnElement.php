<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\DocBlock\DocBlock;

abstract class AbstractFqcnElement implements CodeElementInterface
{
    public function __construct(
        protected int $line,
        protected DocBlock $docBlock,
        protected string $fqcn,
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

    public function getFqcn(): string
    {
        return $this->fqcn;
    }

    /**
     * @inheritDoc
     */
    public function getAttributeNames(): array
    {
        return $this->attributeNames;
    }

    /**
     * @param string[] $attributeNames
     */
    public function setAttributeNames(array $attributeNames): void
    {
        $this->attributeNames = $attributeNames;
    }

    public function hasAttribute(string $attributeName): bool
    {
        return in_array($attributeName, $this->attributeNames);
    }
}
