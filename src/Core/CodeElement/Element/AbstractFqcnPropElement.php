<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\Type\TypeInterface;

abstract class AbstractFqcnPropElement extends AbstractFqcnElement
{
    /**
     * @param string[] $attributeNames
     */
    public function __construct(
        int $line,
        DocBlock $docBlock,
        string $fqcn,
        protected string $propName,
        protected TypeInterface $type,
        protected ?TypeInterface $defaultValueType,
        array $attributeNames,
    ) {
        parent::__construct($line, $docBlock, $fqcn, $attributeNames);
    }

    public function getPropName(): string
    {
        return $this->propName;
    }

    public function getType(): TypeInterface
    {
        return $this->type;
    }

    public function getDefaultValueType(): ?TypeInterface
    {
        return $this->defaultValueType;
    }
}
