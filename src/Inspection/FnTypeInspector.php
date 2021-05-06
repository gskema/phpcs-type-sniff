<?php

namespace Gskema\TypeSniff\Inspection;

use Gskema\TypeSniff\Core\Type\Common\ArrayType;
use Gskema\TypeSniff\Core\Type\Common\UndefinedType;
use Gskema\TypeSniff\Core\Type\Declaration\NullableType;
use Gskema\TypeSniff\Core\Type\DocBlock\NullType;
use Gskema\TypeSniff\Core\Type\TypeConverter;
use Gskema\TypeSniff\Inspection\Subject\AbstractTypeSubject;

class FnTypeInspector
{
    public static function reportMandatoryTypes(AbstractTypeSubject $subject): void
    {
        if ($subject->hasDocTypeTag() || $subject->hasAttribute('ArrayShape')) {
            return;
        }

        // e.g. func1(array $arg1) -> must have DocBlock with TypedArrayType
        if ($subject->getFnType() instanceof UndefinedType) {
            $subject->addFnTypeWarning('Add type declaration for :subject: or create PHPDoc with type hint');
        }
    }

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

        $hasArrayShape = $subject->hasAttribute('ArrayShape');
        if (!$subject->hasDefinedDocType() && !$hasArrayShape) {
            return;
        }

        // Require fnType if possible (check, suggest from docType)
        if ($hasArrayShape) {
            $suggestedFnType = new ArrayType();
        } else {
            $suggestedFnType = TypeConverter::toExampleFnType($subject->getDocType());
        }

        if ($suggestedFnType) {
            $subject->addFnTypeWarning(sprintf('Add type declaration for :subject:, e.g.: "%s"', $suggestedFnType->toString()));
        }
    }
}
