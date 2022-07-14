<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Generator;
use Gskema\TypeSniff\Core\CodeElement\Element\Metadata\ClassMethodMetadata;
use Gskema\TypeSniff\Core\CodeElement\Element\Metadata\InterfaceMethodMetadata;
use Gskema\TypeSniff\Core\CodeElement\Element\Metadata\TraitMethodMetadata;
use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\Func\FunctionParam;
use Gskema\TypeSniff\Core\Func\FunctionSignature;

abstract class AbstractFqcnMethodElement extends AbstractFqcnElement
{
    /**
     * @param string[] $attributeNames
     */
    public function __construct(
        DocBlock $docBlock,
        string $fqcn,
        array $attributeNames,
        protected FunctionSignature $signature,
    ) {
        parent::__construct($signature->getLine(), $docBlock, $fqcn, $attributeNames);
    }

    public function getSignature(): FunctionSignature
    {
        return $this->signature;
    }

    public function getId(): string
    {
        return sprintf('%s::%s()', $this->fqcn, $this->signature->getName());
    }

    /**
     * @return Generator<FunctionParam>|FunctionParam[]
     */
    public function getPromotedPropParams(): Generator
    {
        if ('__construct' === $this->signature->getName()) {
            foreach ($this->signature->getParams() as $param) {
                if ($param->isPromotedProp()) {
                    yield $param;
                }
            }
        }
    }

    abstract public function getMetadata(): ClassMethodMetadata|InterfaceMethodMetadata|TraitMethodMetadata;
}
