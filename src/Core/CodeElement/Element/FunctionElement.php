<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\Func\FunctionSignature;

class FunctionElement implements CodeElementInterface
{
    public function __construct(
        protected int $line,
        protected DocBlock $docBlock,
        protected string $namespace,
        protected FunctionSignature $signature,
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

    public function getSignature(): FunctionSignature
    {
        return $this->signature;
    }

    /**
     * @inheritDoc
     */
    public function getAttributeNames(): array
    {
        return $this->attributeNames;
    }
}
