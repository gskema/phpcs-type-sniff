<?php

namespace Gskema\TypeSniff\Inspection\Subject;

use Gskema\TypeSniff\Core\CodeElement\Element\AbstractFqcnConstElement;
use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\DocBlock\Tag\VarTag;
use Gskema\TypeSniff\Core\Type\Common\UndefinedType;
use Gskema\TypeSniff\Core\Type\TypeInterface;

class ConstTypeSubject extends AbstractTypeSubject
{
    public function __construct(
        ?TypeInterface $docType,
        ?TypeInterface $valueType,
        ?int $docTypeLine,
        int $fnTypeLine,
        string $name,
        DocBlock $docBlock,
        string $id
    ) {
        parent::__construct(
            $docType,
            new UndefinedType(), // not in PHP 7.4 :(
            $valueType,
            $docTypeLine,
            $fnTypeLine,
            $name,
            $docBlock,
            $id
        );
    }

    /**
     * @param AbstractFqcnConstElement $const
     *
     * @return static
     */
    public static function fromElement(AbstractFqcnConstElement $const)
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
            $const->getFqcn() . '::' . $const->getConstName()
        );
    }
}
