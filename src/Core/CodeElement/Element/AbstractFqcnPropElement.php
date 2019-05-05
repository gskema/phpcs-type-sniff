<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\DocBlock\DocBlock;

abstract class AbstractFqcnPropElement extends AbstractFqcnElement
{
    /** @var string */
    protected $propName;

    public function __construct(int $line, DocBlock $docBlock, string $fqcn, string $propName)
    {
        parent::__construct($line, $docBlock, $fqcn);
        $this->propName = $propName;
    }

    public function getPropName(): string
    {
        return $this->propName;
    }
}
