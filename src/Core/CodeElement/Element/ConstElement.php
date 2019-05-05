<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\DocBlock\DocBlock;

class ConstElement implements CodeElementInterface
{
    /** @var int */
    protected $line;

    /** @var DocBlock */
    protected $docBlock;

    /** @var string */
    protected $namespace;

    /** @var string */
    protected $name;

    public function __construct(
        int $line,
        DocBlock $docBlock,
        string $namespace,
        string $name
    ) {
        $this->line = $line;
        $this->docBlock = $docBlock;
        $this->namespace = $namespace;
        $this->name = $name;
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
}
