<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\CodeElement\Element\Metadata\TraitPropMetadata;
use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\Type\TypeInterface;

class TraitPropElement extends AbstractFqcnPropElement
{
    /** @var TraitPropMetadata */
    protected $metadata;

    public function __construct(
        int $line,
        DocBlock $docBlock,
        string $fqcn,
        string $propName,
        ?TypeInterface $defaultValueType,
        ?TraitPropMetadata $metadata = null
    ) {
        parent::__construct($line, $docBlock, $fqcn, $propName, $defaultValueType);
        $this->metadata = $metadata ?? new TraitPropMetadata();
    }

    public function getMetadata(): TraitPropMetadata
    {
        return $this->metadata;
    }
}
