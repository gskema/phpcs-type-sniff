<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\DocBlock\DocBlock;

class ClassElement extends AbstractFqcnElement
{
    /** @var ClassConstElement[] */
    protected $constants = [];

    /** @var ClassPropElement[] */
    protected $properties = [];

    /** @var ClassMethodElement[] */
    protected $methods = [];

    /**
     * @param int                   $line
     * @param DocBlock              $docBlock
     * @param string                $fqcn
     * @param ClassConstElement[]   $constants
     * @param ClassPropElement[]    $properties
     * @param ClassMethodElement[]  $methods
     */
    public function __construct(
        int $line,
        DocBlock $docBlock,
        string $fqcn,
        array $constants = [],
        array $properties = [],
        array $methods = []
    ) {
        parent::__construct($line, $docBlock, $fqcn);
        $this->constants = $constants;
        $this->properties = $properties;
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
        $this->properties[] = $prop;
    }

    public function addMethod(ClassMethodElement $method): void
    {
        $this->methods[$method->getSignature()->getName()] = $method;
    }

    public function getOwnConstructor(): ?ClassMethodElement
    {
        return $this->methods['__construct'] ?? null;
    }
}
