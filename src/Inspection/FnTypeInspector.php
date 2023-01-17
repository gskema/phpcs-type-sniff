<?php

namespace Gskema\TypeSniff\Inspection;

use Gskema\TypeSniff\Core\Type\Common\ArrayType;
use Gskema\TypeSniff\Core\Type\Common\FalseType;
use Gskema\TypeSniff\Core\Type\Common\MixedType;
use Gskema\TypeSniff\Core\Type\Common\NullType;
use Gskema\TypeSniff\Core\Type\Common\TrueType;
use Gskema\TypeSniff\Core\Type\Common\UndefinedType;
use Gskema\TypeSniff\Core\Type\Common\UnionType;
use Gskema\TypeSniff\Core\Type\Declaration\NullableType;
use Gskema\TypeSniff\Core\Type\TypeConverter;
use Gskema\TypeSniff\Core\Type\TypeInterface;
use Gskema\TypeSniff\Inspection\Subject\AbstractTypeSubject;
use Gskema\TypeSniff\Inspection\Subject\PropTypeSubject;

class FnTypeInspector
{
    public static function reportReplaceableTypes(AbstractTypeSubject $subject): void
    {
        $fnType = $subject->getFnType();

        // (string $arg1 = null) -> (?string $arg1 = null)
        if (
            $subject->getValueType() instanceof NullType
            && !($fnType instanceof UndefinedType)
            && !($fnType instanceof NullableType)
            && !($fnType instanceof UnionType)
            && !($fnType instanceof MixedType)
        ) {
            $subject->addFnTypeWarning(sprintf(
                'Change :subject: type declaration to nullable, e.g. %s. Remove default null value if this argument is required.',
                (new NullableType($fnType))->toString(),
            ));
        }

        // (string|null $param1) -> (?string $param1)
        if (
            $fnType instanceof UnionType &&
            $fnType->getCount() === 2 &&
            $fnType->containsType(NullType::class)
        ) {
            $otherSubTypes = array_filter($fnType->getTypes(), fn(TypeInterface $type) => !($type instanceof NullType));
            $otherSubType = reset($otherSubTypes);
            if (
                !($otherSubType instanceof MixedType) &&
                !($otherSubType instanceof FalseType) &&
                !($otherSubType instanceof TrueType)
            ) {
                $subject->addFnTypeWarning(sprintf(
                    'Change :subject: type declaration to shorthand nullable syntax, e.g. %s',
                    (new NullableType($otherSubType))->toString(),
                ));
            }
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
