<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\DocBlock\DocBlock;

class TraitElement extends AbstractFqcnElement
{
    /** @var TraitPropElement[] */
    protected $properties = [];

    /** @var TraitMethodElement[] */
    protected $methods = [];

    /**
     * @param int                  $line
     * @param DocBlock             $docBlock
     * @param string               $fqcn
     * @param TraitPropElement[]   $properties
     * @param TraitMethodElement[] $methods
     */
    public function __construct(
        int $line,
        DocBlock $docBlock,
        string $fqcn,
        array $properties = [],
        array $methods = []
    ) {
        parent::__construct($line, $docBlock, $fqcn);
        $this->properties = $properties;
        $this->methods = $methods;
    }

    /**
     * @return TraitPropElement[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @return TraitMethodElement[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    public function addProperty(TraitPropElement $prop): void
    {
        $this->properties[] = $prop;
    }

    public function addMethod(TraitMethodElement $method): void
    {
        $this->methods[] = $method;
    }
}
