<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\Func\FunctionSignature;

class FunctionElement implements CodeElementInterface
{
    /** @var int */
    protected $line;

    /** @var DocBlock */
    protected $docBlock;

    /** @var string */
    protected $namespace;

    /** @var FunctionSignature */
    protected $signature;

    public function __construct(
        int $line,
        DocBlock $docBlock,
        string $namespace,
        FunctionSignature $signature
    ) {
        $this->line = $line;
        $this->docBlock = $docBlock;
        $this->namespace = $namespace;
        $this->signature = $signature;
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
}
