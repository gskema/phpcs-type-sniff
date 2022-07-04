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
     * @param TypeInterface|null $docType
     * @param TypeInterface|null $valueType
     * @param int|null           $docTypeLine
     * @param int                $fnTypeLine
     * @param string             $name
     * @param DocBlock           $docBlock
     * @param string[]           $attributeNames
     * @param string             $id
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

    /**
     * @param AbstractFqcnConstElement $const
     *
     * @return static
     */
    public static function fromElement(AbstractFqcnConstElement $const): static
    {
        $docBlock = $const->getDocBlock();

        /** @var VarTag|null $varTag */
        $varTag = $docBlock->getTagsByName('var')[0] ?? null;

        return new static(
            $varTag ? $varTag->getType() : null,
            $const->getValueType(),
            $varTag ? $varTag->getLine() : null,
            $const->getLine(),
            $const->getConstName() . ' constant',
            $docBlock,
            $const->getAttributeNames(),
            $const->getFqcn() . '::' . $const->getConstName(),
        );
    }
}
