<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

class TraitElement extends AbstractFqcnElement
{
    /** @var TraitPropElement[] */
    protected $properties = [];

    /** @var TraitMethodElement[] */
    protected $methods = [];

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
