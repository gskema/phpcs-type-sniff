<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\CodeElement\Element\Metadata\InterfaceMethodMetadata;
use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\Func\FunctionSignature;

class InterfaceMethodElement extends AbstractFqcnMethodElement
{
    /** @var InterfaceMethodMetadata */
    protected $metadata;

    public function __construct(
        DocBlock $docBlock,
        string $fqcn,
        FunctionSignature $signature,
        ?InterfaceMethodMetadata $metadata = null
    ) {
        parent::__construct($docBlock, $fqcn, $signature);
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
