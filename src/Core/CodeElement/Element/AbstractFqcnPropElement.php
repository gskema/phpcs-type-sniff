<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\Type\TypeInterface;

abstract class AbstractFqcnPropElement extends AbstractFqcnElement
{
    protected string $propName;

    protected TypeInterface $type;

    protected ?TypeInterface $defaultValueType;

    /**
     * @param int                $line
     * @param DocBlock           $docBlock
     * @param string             $fqcn
     * @param string             $propName
     * @param TypeInterface      $type
     * @param TypeInterface|null $defaultValueType
     * @param string[]           $attributeNames
     */
    public function __construct(
        int $line,
        DocBlock $docBlock,
        string $fqcn,
        string $propName,
        TypeInterface $type,
        ?TypeInterface $defaultValueType,
        array $attributeNames,
    ) {
        parent::__construct($line, $docBlock, $fqcn, $attributeNames);
        $this->propName = $propName;
        $this->type = $type;
        $this->defaultValueType = $defaultValueType;
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
