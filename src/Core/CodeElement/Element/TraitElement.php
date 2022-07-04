<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\DocBlock\DocBlock;

class TraitElement extends AbstractFqcnElement
{
    /** @var TraitPropElement[] */
    protected array $properties = [];

    /** @var TraitMethodElement[] */
    protected array $methods = [];

    /**
     * @param int                  $line
     * @param DocBlock             $docBlock
     * @param string               $fqcn
     * @param TraitPropElement[]   $properties
     * @param TraitMethodElement[] $methods
     * @param string[]             $attributeNames
     */
    public function __construct(
        int $line,
        DocBlock $docBlock,
        string $fqcn,
        array $properties = [],
        array $methods = [],
        array $attributeNames = [],
    ) {
        parent::__construct($line, $docBlock, $fqcn, $attributeNames);
        $this->properties = $properties;
        array_walk($methods, [$this, 'addMethod']);
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

    public function getOwnConstructor(): ?TraitMethodElement
    {
        return $this->methods['__construct'] ?? null;
    }

    public function getMethod(string $name): ?TraitMethodElement
    {
        return $this->methods[$name] ?? null;
    }
}
