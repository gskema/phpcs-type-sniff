<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\CodeElement\Element\Metadata\ClassPropMetadata;
use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\Type\TypeInterface;

class ClassPropElement extends AbstractFqcnPropElement
{
    protected ClassPropMetadata $metadata;

    /**
     * @param string[] $attributeNames
     */
    public function __construct(
        int $line,
        DocBlock $docBlock,
        string $fqcn,
        array $attributeNames,
        string $propName,
        TypeInterface $type,
        ?TypeInterface $defaultValueType,
        ?ClassPropMetadata $metadata = null,
    ) {
        parent::__construct($line, $docBlock, $fqcn, $attributeNames, $propName, $type, $defaultValueType);
        $this->metadata = $metadata ?? new ClassPropMetadata();
    }

    public function getMetadata(): ClassPropMetadata
    {
        return $this->metadata;
    }
}
