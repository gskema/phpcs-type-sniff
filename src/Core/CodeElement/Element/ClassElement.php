<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\DocBlock\DocBlock;

class ClassElement extends AbstractFqcnElement
{
    /** @var ClassConstElement[] */
    protected array $constants = [];

    /** @var ClassPropElement[] */
    protected array $properties = [];

    /** @var ClassMethodElement[] */
    protected array $methods = [];

    /**
     * @param int                   $line
     * @param DocBlock              $docBlock
     * @param string                $fqcn
     * @param ClassConstElement[]   $constants
     * @param ClassPropElement[]    $properties
     * @param ClassMethodElement[]  $methods
     * @param string[]              $attributeNames
     */
    public function __construct(
        int $line,
        DocBlock $docBlock,
        string $fqcn,
        array $constants = [],
        array $properties = [],
        array $methods = [],
        array $attributeNames = []
    ) {
        parent::__construct($line, $docBlock, $fqcn, $attributeNames);
        $this->constants = $constants;
        array_walk($properties, [$this, 'addProperty']);
        array_walk($methods, [$this, 'addMethod']);
    }

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
        $this->properties[$prop->getPropName()] = $prop;
    }

    public function addMethod(ClassMethodElement $method): void
    {
        $this->methods[$method->getSignature()->getName()] = $method;
    }

    public function getOwnConstructor(): ?ClassMethodElement
    {
        return $this->methods['__construct'] ?? null;
    }

    public function getMethod(string $name): ?ClassMethodElement
    {
        return $this->methods[$name] ?? null;
    }

    public function getProperty(string $name): ?ClassPropElement
    {
        return $this->properties[$name] ?? null;
    }
}
