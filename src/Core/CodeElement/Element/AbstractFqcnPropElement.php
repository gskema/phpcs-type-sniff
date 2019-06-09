<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\Type\TypeInterface;

abstract class AbstractFqcnPropElement extends AbstractFqcnElement
{
    /** @var string */
    protected $propName;

    /** @var TypeInterface|null */
    protected $defaultValueType;

    public function __construct(
        int $line,
        DocBlock $docBlock,
        string $fqcn,
        string $propName,
        ?TypeInterface $defaultValueType
    ) {
        parent::__construct($line, $docBlock, $fqcn);
        $this->propName = $propName;
        $this->defaultValueType = $defaultValueType;
    }

    public function getPropName(): string
    {
        return $this->propName;
    }

    public function getDefaultValueType(): ?TypeInterface
    {
        return $this->defaultValueType;
    }
}
