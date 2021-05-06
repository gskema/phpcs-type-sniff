<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\CodeElement\Element\Metadata\ClassMethodMetadata;
use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\Func\FunctionSignature;

class ClassMethodElement extends AbstractFqcnMethodElement
{
    /** @var ClassMethodMetadata */
    protected $metadata;

    /**
     * @param DocBlock                 $docBlock
     * @param string                   $fqcn
     * @param FunctionSignature        $signature
     * @param ClassMethodMetadata|null $metadata
     * @param string[]                 $attributeNames
     */
    public function __construct(
        DocBlock $docBlock,
        string $fqcn,
        FunctionSignature $signature,
        ?ClassMethodMetadata $metadata = null,
        array $attributeNames = []
    ) {
        parent::__construct($docBlock, $fqcn, $signature, $attributeNames);
        $this->metadata = $metadata ?? new ClassMethodMetadata();
    }

    /**
     * @inheritDoc
     */
    public function getMetadata(): ClassMethodMetadata
    {
        return $this->metadata;
    }
}
