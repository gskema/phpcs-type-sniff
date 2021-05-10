<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\Type\TypeInterface;

abstract class AbstractFqcnConstElement extends AbstractFqcnElement
{
    protected string $constName;

    protected ?TypeInterface $valueType;

    /**
     * @param int                $line
     * @param DocBlock           $docBlock
     * @param string             $fqcn
     * @param string             $constName
     * @param TypeInterface|null $valueType
     * @param string[]           $attributeNames
     */
    public function __construct(
        int $line,
        DocBlock $docBlock,
        string $fqcn,
        string $constName,
        ?TypeInterface $valueType,
        array $attributeNames
    ) {
        parent::__construct($line, $docBlock, $fqcn, $attributeNames);
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
