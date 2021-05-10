<?php

namespace Gskema\TypeSniff\Inspection;

use Gskema\TypeSniff\Core\Type\Common\ArrayType;
use Gskema\TypeSniff\Core\Type\Common\UndefinedType;
use Gskema\TypeSniff\Core\Type\Declaration\NullableType;
use Gskema\TypeSniff\Core\Type\DocBlock\NullType;
use Gskema\TypeSniff\Core\Type\TypeConverter;
use Gskema\TypeSniff\Inspection\Subject\AbstractTypeSubject;
use Gskema\TypeSniff\Inspection\Subject\PropTypeSubject;

class FnTypeInspector
{
    public static function reportReplaceableTypes(AbstractTypeSubject $subject): void
    {
        // (string $arg1 = null) -> (?string $arg1 = null)
        if (
            $subject->getValueType() instanceof NullType
            && !($subject->getFnType() instanceof UndefinedType)
            && !($subject->getFnType() instanceof NullableType)
        ) {
            $subject->addFnTypeWarning(sprintf(
                'Change :subject: type declaration to nullable, e.g. %s. Remove default null value if this argument is required.',
                (new NullableType($subject->getFnType()))->toString()
            ));
        }
    }

    public static function reportSuggestedTypes(AbstractTypeSubject $subject): void
    {
        if ($subject->hasDefinedFnType()) {
            return;
        }

        $isProp = $subject instanceof PropTypeSubject;
        $valueType = $subject->getValueType();
        $hasDefaultValue = $valueType && !($valueType instanceof UndefinedType);
        $hasDocType = $subject->hasDefinedDocType() || $subject->hasAttribute('ArrayShape');

        $requestAnyType = false;
        if ($subject->hasDefinedDocType()) {
            $possibleFnType = TypeConverter::toExampleFnType($subject->getDocType(), $isProp);
        } elseif ($subject->hasAttribute('ArrayShape')) {
            $possibleFnType = new ArrayType();
        } else {
            // Doc type and ArrayShape undefined, require any type decl. or suggest from value type.
            $possibleFnType = $hasDefaultValue ? TypeConverter::toExampleFnType($valueType, $isProp) : null;
            $requestAnyType = true;
        }

        if ($possibleFnType || $requestAnyType) {
            $warning = 'Add type declaration for :subject:';
            $warning .= $possibleFnType ? sprintf(', e.g.: "%s"', $possibleFnType->toString()) : '';
            $warning .= $hasDocType ? '' : ' or create PHPDoc with type hint';
            $warning .= $isProp && !$hasDefaultValue ? '. Add default value or keep property in an uninitialized state.' : '.';
            $subject->addFnTypeWarning($warning);
        }
    }
}
