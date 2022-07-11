<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\Type\TypeInterface;

abstract class AbstractFqcnConstElement extends AbstractFqcnElement
{
    /**
     * @param string[] $attributeNames
     */
    public function __construct(
        int $line,
        DocBlock $docBlock,
        string $fqcn,
        protected string $constName,
        protected ?TypeInterface $valueType,
        array $attributeNames,
    ) {
        parent::__construct($line, $docBlock, $fqcn, $attributeNames);
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
