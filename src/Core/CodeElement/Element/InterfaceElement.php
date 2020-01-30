<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\DocBlock\DocBlock;

class InterfaceElement extends AbstractFqcnElement
{
    /** @var InterfaceConstElement[] */
    protected $constants = [];

    /** @var InterfaceMethodElement[] */
    protected $methods = [];

    /**
     * @param int                      $line
     * @param DocBlock                 $docBlock
     * @param string                   $fqcn
     * @param InterfaceConstElement[]  $constants
     * @param InterfaceMethodElement[] $methods
     */
    public function __construct(
        int $line,
        DocBlock $docBlock,
        string $fqcn,
        array $constants = [],
        array $methods = []
    ) {
        parent::__construct($line, $docBlock, $fqcn);
        $this->constants = $constants;
        $this->methods = $methods;
    }

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
