<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

class InterfaceElement extends AbstractFqcnElement
{
    /** @var InterfaceConstElement[] */
    protected $constants = [];

    /** @var InterfaceMethodElement[] */
    protected $methods = [];

    /**
     * @return InterfaceConstElement[]
     */
    public function getConstants(): array
    {
        return $this->constants;
    }

    /**
     * @return InterfaceMethodElement[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    public function addConstant(InterfaceConstElement $constant): void
    {
        $this->constants[] = $constant;
    }

    public function addMethod(InterfaceMethodElement $method): void
    {
        $this->methods[] = $method;
    }
}
