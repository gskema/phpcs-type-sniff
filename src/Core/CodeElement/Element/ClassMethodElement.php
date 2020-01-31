<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\CodeElement\Element\Metadata\ClassMethodMetadata;
use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\Func\FunctionSignature;

class ClassMethodElement extends AbstractFqcnMethodElement
{
    /** @var ClassMethodMetadata */
    protected $metadata;

    public function __construct(
        DocBlock $docBlock,
        string $fqcn,
        FunctionSignature $signature,
        ?ClassMethodMetadata $metadata = null
    ) {
        parent::__construct($docBlock, $fqcn, $signature);
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
