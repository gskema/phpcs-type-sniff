<?php

namespace Gskema\TypeSniff\Inspection;

use Gskema\TypeSniff\Core\Type\Common\UndefinedType;
use Gskema\TypeSniff\Core\Type\Declaration\NullableType;
use Gskema\TypeSniff\Core\Type\DocBlock\NullType;
use Gskema\TypeSniff\Core\Type\TypeConverter;

class FnTypeInspector
{
    public static function reportExpectedNullableType(TypeSubject $subject): void
    {
        // (string $arg1 = null) -> (?string $arg1 = null)
        if ($subject->getValueType() instanceof NullType
            && !($subject->getFnType() instanceof UndefinedType)
            && !($subject->getFnType() instanceof NullableType)
        ) {
            $subject->addFnTypeWarning(sprintf(
                'Change :subject: type declaration to nullable, e.g. %s. Remove default null value if this argument is required.',
                (new NullableType($subject->getFnType()))->toString()
            ));
        }
    }

    public static function reportSuggestedFnTypes(TypeSubject $subject): void
    {
        // Require fnType if possible (check, suggest from docType)
        if ($suggestedFnType = TypeConverter::toExampleFnType($subject->getDocType())) {
            $subject->addFnTypeWarning(sprintf('Add type declaration for :subject:, e.g.: "%s"', $suggestedFnType->toString()));
        }
    }
}
