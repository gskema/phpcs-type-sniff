<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\DocBlock\DocBlock;

abstract class AbstractFqcnElement implements CodeElementInterface
{
    /** @var int */
    protected $line;

    /** @var DocBlock */
    protected $docBlock;

    /** @var string */
    protected $fqcn;

    public function __construct(int $line, DocBlock $docBlock, string $fqcn)
    {
        $this->line = $line;
        $this->docBlock = $docBlock;
        $this->fqcn = $fqcn;
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
}
