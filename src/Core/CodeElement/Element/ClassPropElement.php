<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\CodeElement\Element\Metadata\ClassPropMetadata;
use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\Type\TypeInterface;

class ClassPropElement extends AbstractFqcnPropElement
{
    /** @var ClassPropMetadata */
    protected $metadata;

    /**
     * @param int                    $line
     * @param DocBlock               $docBlock
     * @param string                 $fqcn
     * @param string                 $propName
     * @param TypeInterface|null     $defaultValueType
     * @param ClassPropMetadata|null $metadata
     * @param string[]               $attributeNames
     */
    public function __construct(
        int $line,
        DocBlock $docBlock,
        string $fqcn,
        string $propName,
        ?TypeInterface $defaultValueType,
        ?ClassPropMetadata $metadata = null,
        array $attributeNames = []
    ) {
        parent::__construct($line, $docBlock, $fqcn, $propName, $defaultValueType, $attributeNames);
        $this->metadata = $metadata ?? new ClassPropMetadata();
    }

    public function getMetadata(): ClassPropMetadata
    {
        return $this->metadata;
    }
}
