<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\DocBlock\DocBlock;

class EnumElement extends AbstractFqcnElement
{
    // cases - not used or needed at the moment.

    /**
     * @param string[] $attributeNames
     */
    public function __construct(
        int $line,
        DocBlock $docBlock,
        string $fqcn,
        array $attributeNames = [],
        /** @var EnumConstElement[] */
        protected array $constants = [],
        /** @var EnumMethodElement[] */
        protected array $methods = [],
    ) {
        parent::__construct($line, $docBlock, $fqcn, $attributeNames);
    }

    /**
     * @return EnumConstElement[]
     */
    public function getConstants(): array
    {
        return $this->constants;
    }

    /**
     * @return EnumMethodElement[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    public function addConstant(EnumConstElement $const): void
    {
        $this->constants[] = $const;
    }

    public function addMethod(EnumMethodElement $method): void
    {
        $this->methods[] = $method;
    }
}
