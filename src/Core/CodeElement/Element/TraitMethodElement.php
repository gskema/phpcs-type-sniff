<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\CodeElement\Element\Metadata\TraitMethodMetadata;
use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\Func\FunctionSignature;

class TraitMethodElement extends AbstractFqcnMethodElement
{
    /** @var TraitMethodMetadata */
    protected $metadata;

    public function __construct(
        DocBlock $docBlock,
        string $fqcn,
        FunctionSignature $signature,
        ?TraitMethodMetadata $metadata = null
    ) {
        parent::__construct($docBlock, $fqcn, $signature);
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
