<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\CodeElement\Element\Metadata\InterfaceMethodMetadata;
use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\Func\FunctionSignature;

class InterfaceMethodElement extends AbstractFqcnMethodElement
{
    protected InterfaceMethodMetadata $metadata;

    /**
     * @param string[] $attributeNames
     */
    public function __construct(
        DocBlock $docBlock,
        string $fqcn,
        FunctionSignature $signature,
        ?InterfaceMethodMetadata $metadata = null,
        array $attributeNames = [],
    ) {
        parent::__construct($docBlock, $fqcn, $signature, $attributeNames);
        $this->metadata = $metadata ?? new InterfaceMethodMetadata();
    }

    /**
     * @inheritDoc
     */
    public function getMetadata(): InterfaceMethodMetadata
    {
        return $this->metadata;
    }
}
