<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\Func\FunctionSignature;

class FunctionElement implements CodeElementInterface
{
    protected int $line;

    protected DocBlock $docBlock;

    protected string $namespace;

    protected FunctionSignature $signature;

    /** @var string[] */
    protected array $attributeNames = [];

    /**
     * @param int               $line
     * @param DocBlock          $docBlock
     * @param string            $namespace
     * @param FunctionSignature $signature
     * @param string[]          $attributeNames
     */
    public function __construct(
        int $line,
        DocBlock $docBlock,
        string $namespace,
        FunctionSignature $signature,
        array $attributeNames
    ) {
        $this->line = $line;
        $this->docBlock = $docBlock;
        $this->namespace = $namespace;
        $this->signature = $signature;
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
