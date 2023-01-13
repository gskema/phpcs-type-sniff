<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\CodeElement\Element\Metadata\ClassPropMetadata;
use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\Func\FunctionParam;
use Gskema\TypeSniff\Core\Type\Common\UndefinedType;
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
        bool $promoted,
        ?ClassPropMetadata $metadata = null,
    ) {
        parent::__construct($line, $docBlock, $fqcn, $attributeNames, $propName, $type, $defaultValueType, $promoted);
        $this->metadata = $metadata ?? new ClassPropMetadata();
    }

    public static function fromFunctionParam(FunctionParam $param, string $fqcn): static
    {
        return new static(
            $param->getLine(),
            $param->getDocBlock(),
            $fqcn,
            $param->getAttributeNames(),
            $param->getName(),
            $param->getType(),
            $param->getValueType(),
            $param->isPromotedProp(),
            new ClassPropMetadata(
                $param->getValueType() && !is_a($param->getValueType(), UndefinedType::class)
            )
        );
    }

    public function getMetadata(): ClassPropMetadata
    {
        return $this->metadata;
    }
}
