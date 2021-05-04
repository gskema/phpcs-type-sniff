<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\CodeElement\Element\Metadata\TraitMethodMetadata;
use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\Func\FunctionSignature;

class TraitMethodElement extends AbstractFqcnMethodElement
{
    /** @var TraitMethodMetadata */
    protected $metadata;

    /**
     * @param DocBlock                 $docBlock
     * @param string                   $fqcn
     * @param FunctionSignature        $signature
     * @param TraitMethodMetadata|null $metadata
     * @param string[]                 $attributeNames
     */
    public function __construct(
        DocBlock $docBlock,
        string $fqcn,
        FunctionSignature $signature,
        ?TraitMethodMetadata $metadata = null,
        array $attributeNames = []
    ) {
        parent::__construct($docBlock, $fqcn, $signature, $attributeNames);
        $this->metadata = $metadata ?? new TraitMethodMetadata();
    }

    /**
     * @inheritDoc
     */
    public function getMetadata(): TraitMethodMetadata
    {
        return $this->metadata;
    }
}
