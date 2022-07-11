<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\DocBlock\DocBlock;

class TraitElement extends AbstractFqcnElement
{
    /** @var TraitMethodElement[] */
    protected array $methods = [];

    /**
     * @param string[]             $attributeNames
     * @param TraitMethodElement[] $methods
     */
    public function __construct(
        int $line,
        DocBlock $docBlock,
        string $fqcn,
        array $attributeNames = [],
        /** @var TraitPropElement[] */
        protected array $properties = [],
        array $methods = [],
    ) {
        parent::__construct($line, $docBlock, $fqcn, $attributeNames);
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
