<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\DocBlock\DocBlock;

abstract class AbstractFqcnConstElement extends AbstractFqcnElement
{
    /** @var string */
    protected $constName;

    public function __construct(int $line, DocBlock $docBlock, string $fqcn, string $constName)
    {
        parent::__construct($line, $docBlock, $fqcn);
        $this->constName = $constName;
    }

    public function getConstName(): string
    {
        return $this->constName;
    }
}
