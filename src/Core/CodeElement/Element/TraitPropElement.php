<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\CodeElement\Element\Metadata\TraitPropMetadata;
use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\Type\TypeInterface;

class TraitPropElement extends AbstractFqcnPropElement
{
    protected TraitPropMetadata $metadata;

    /**
     * @param string[] $attributeNames
     */
    public function __construct(
        int $line,
        DocBlock $docBlock,
        string $fqcn,
        string $propName,
        TypeInterface $type,
        ?TypeInterface $defaultValueType,
        ?TraitPropMetadata $metadata = null,
        array $attributeNames = [],
    ) {
        parent::__construct($line, $docBlock, $fqcn, $propName, $type, $defaultValueType, $attributeNames);
        $this->metadata = $metadata ?? new TraitPropMetadata();
    }

    public function getMetadata(): TraitPropMetadata
    {
        return $this->metadata;
    }
}
