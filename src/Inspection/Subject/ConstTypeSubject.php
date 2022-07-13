<?php

namespace Gskema\TypeSniff\Inspection\Subject;

use Gskema\TypeSniff\Core\CodeElement\Element\AbstractFqcnConstElement;
use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\DocBlock\Tag\VarTag;
use Gskema\TypeSniff\Core\Type\Common\UndefinedType;
use Gskema\TypeSniff\Core\Type\TypeInterface;

class ConstTypeSubject extends AbstractTypeSubject
{
    /**
     * @param string[] $attributeNames
     */
    public function __construct(
        ?TypeInterface $docType,
        ?TypeInterface $valueType,
        ?int $docTypeLine,
        int $fnTypeLine,
        string $name,
        DocBlock $docBlock,
        array $attributeNames,
        string $id,
    ) {
        parent::__construct(
            $docType,
            new UndefinedType(), // not in PHP 7.4 :(
            $valueType,
            $docTypeLine,
            $fnTypeLine,
            $name,
            $docBlock,
            $attributeNames,
            $id,
        );
    }

    public static function fromElement(AbstractFqcnConstElement $const): static
    {
        $docBlock = $const->getDocBlock();

        /** @var VarTag|null $varTag */
        $varTag = $docBlock->getTagsByName('var')[0] ?? null;

        return new static(
            $varTag?->getType(),
            $const->getValueType(),
            $varTag?->getLine(),
            $const->getLine(),
            $const->getConstName() . ' constant',
            $docBlock,
            $const->getAttributeNames(),
            $const->getFqcn() . '::' . $const->getConstName(),
        );
    }
}
