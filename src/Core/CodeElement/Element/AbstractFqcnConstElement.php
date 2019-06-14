<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\Type\TypeInterface;

abstract class AbstractFqcnConstElement extends AbstractFqcnElement
{
    /** @var string */
    protected $constName;

    /** @var TypeInterface|null */
    protected $valueType;

    public function __construct(
        int $line,
        DocBlock $docBlock,
        string $fqcn,
        string $constName,
        ?TypeInterface $valueType
    ) {
        parent::__construct($line, $docBlock, $fqcn);
        $this->constName = $constName;
        $this->valueType = $valueType;
    }

    public function getConstName(): string
    {
        return $this->constName;
    }

    public function getValueType(): ?TypeInterface
    {
        return $this->valueType;
    }
}
