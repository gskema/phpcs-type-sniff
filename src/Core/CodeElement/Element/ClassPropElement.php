<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\CodeElement\Element\Metadata\ClassPropMetadata;
use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\Type\TypeInterface;

class ClassPropElement extends AbstractFqcnPropElement
{
    /** @var ClassPropMetadata */
    protected $metadata;

    public function __construct(
        int $line,
        DocBlock $docBlock,
        string $fqcn,
        string $propName,
        ?TypeInterface $defaultValueType,
        ?ClassPropMetadata $metadata = null
    ) {
        parent::__construct($line, $docBlock, $fqcn, $propName, $defaultValueType);
        $this->metadata = $metadata ?? new ClassPropMetadata();
    }

    public function getMetadata(): ClassPropMetadata
    {
        return $this->metadata;
    }
}
