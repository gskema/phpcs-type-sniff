<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

class ClassElement extends AbstractFqcnElement
{
    /** @var ClassConstElement[] */
    protected $constants = [];

    /** @var ClassPropElement[] */
    protected $properties = [];

    /** @var ClassMethodElement[] */
    protected $methods = [];

    /**
     * @return ClassConstElement[]
     */
    public function getConstants(): array
    {
        return $this->constants;
    }

    /**
     * @return ClassPropElement[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @return ClassMethodElement[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    public function addConstant(ClassConstElement $constant): void
    {
        $this->constants[] = $constant;
    }

    public function addProperty(ClassPropElement $prop): void
    {
        $this->properties[] = $prop;
    }

    public function addMethod(ClassMethodElement $method): void
    {
        $this->methods[] = $method;
    }
}
