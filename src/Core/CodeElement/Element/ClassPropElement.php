<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\CodeElement\Element\Metadata\ClassPropMetadata;
use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\Type\TypeInterface;

class ClassPropElement extends AbstractFqcnPropElement
{
    protected ClassPropMetadata $metadata;

    /**
     * @param int                    $line
     * @param DocBlock               $docBlock
     * @param string                 $fqcn
     * @param string                 $propName
     * @param TypeInterface          $type
     * @param TypeInterface|null     $defaultValueType
     * @param ClassPropMetadata|null $metadata
     * @param string[]               $attributeNames
     */
    public function __construct(
        int $line,
        DocBlock $docBlock,
        string $fqcn,
        string $propName,
        TypeInterface $type,
        ?TypeInterface $defaultValueType,
        ?ClassPropMetadata $metadata = null,
        array $attributeNames = [],
    ) {
        parent::__construct($line, $docBlock, $fqcn, $propName, $type, $defaultValueType, $attributeNames);
        $this->metadata = $metadata ?? new ClassPropMetadata();
    }

    public function getMetadata(): ClassPropMetadata
    {
        return $this->metadata;
    }
}
