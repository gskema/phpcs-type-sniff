<?php

namespace Gskema\TypeSniff\Inspection\Subject;

use Gskema\TypeSniff\Core\CodeElement\Element\AbstractFqcnPropElement;
use Gskema\TypeSniff\Core\DocBlock\Tag\VarTag;
use Gskema\TypeSniff\Core\Type\Common\UndefinedType;

class PropTypeSubject extends AbstractTypeSubject
{
    /**
     * @param AbstractFqcnPropElement $prop
     *
     * @return static
     */
    public static function fromElement(AbstractFqcnPropElement $prop)
    {
        $docBlock = $prop->getDocBlock();

        /** @var VarTag|null $varTag */
        $varTag = $docBlock->getTagsByName('var')[0] ?? null;

        return new static(
            $varTag ? $varTag->getType() : null,
            new UndefinedType(),
            $prop->getDefaultValueType(),
            $varTag ? $varTag->getLine() : $prop->getLine(),
            $prop->getLine(),
            'property $' . $prop->getPropName(),
            $docBlock
        );
    }
}
