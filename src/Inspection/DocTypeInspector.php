<?php

namespace Gskema\TypeSniff\Inspection;

use Gskema\TypeSniff\Core\Type\Common\ArrayType;
use Gskema\TypeSniff\Core\Type\Common\VoidType;
use Gskema\TypeSniff\Core\Type\Declaration\NullableType;
use Gskema\TypeSniff\Core\Type\DocBlock\NullType;
use Gskema\TypeSniff\Core\Type\TypeComparator;
use Gskema\TypeSniff\Core\Type\TypeConverter;
use Gskema\TypeSniff\Core\Type\TypeHelper;
use Gskema\TypeSniff\Inspection\Subject\AbstractTypeSubject;
use Gskema\TypeSniff\Inspection\Subject\ParamTypeSubject;
use Gskema\TypeSniff\Inspection\Subject\PropTypeSubject;
use Gskema\TypeSniff\Inspection\Subject\ReturnTypeSubject;

class DocTypeInspector
{
    public static function reportRequiredTypedArrayType(AbstractTypeSubject $subject): void
    {
        if ($subject->hasDefinedDocBlock()) {
            return;
        }

        if ($subject->getValueType() instanceof ArrayType
            || TypeHelper::containsType($subject->getFnType(), ArrayType::class)
        ) {
            $isNullable = $subject->getFnType() instanceof NullableType;
            $subject->addFnTypeWarning(sprintf(
                'Create PHPDoc with typed array type hint for :subject:, .e.g.: "string[]%s" or "SomeClass[]%s". Correct array depth must be specified.',
                $isNullable ? '|null' : '',
                $isNullable ? '|null' : ''
            ));
        }
    }

    public static function reportMissingTypedArrayTypes(AbstractTypeSubject $subject): void
    {
        if (!$subject->hasDefinedDocType()) {
            return;
        }

        // e.g. @param array $arg1 -> @param int[] $arg1
        if (TypeHelper::containsType($subject->getDocType(), ArrayType::class)) {
            $subject->addDocTypeWarning(
                'Replace array type with typed array type in PHPDoc for :subject:, .e.g.: "string[]" or "SomeClass[]". Use mixed[] for generic arrays. Correct array depth must be specified.'
            );
        }
    }

    public static function reportMissingTag(AbstractTypeSubject $subject): void
    {
        if (!($subject->hasDefinedDocBlock() && null === $subject->getDocType())) {
            return;
        }

        // e.g. DocBlock exists, but no @param tag
        if ($subject instanceof ReturnTypeSubject) {
            if (!($subject->getFnType() instanceof VoidType)) {
                $subject->addFnTypeWarning('Missing PHPDoc tag or void type declaration for :subject:');
            }
        } else {
            $subject->addFnTypeWarning('Missing PHPDoc tag for :subject:');
        }
    }

    public static function reportUnnecessaryVoidTag(AbstractTypeSubject $subject): void
    {
        // @return void in not needed
        if ($subject instanceof ReturnTypeSubject
            && $subject->getFnType() instanceof VoidType
            && $subject->getDocType() instanceof VoidType
        ) {
            $subject->addDocTypeWarning('Remove @return void tag, not necessary');
        }
    }

    public static function reportRedundantTypes(AbstractTypeSubject $subject): void
    {
        if (!$subject->hasDefinedDocType()) {
            return;
        }

        // e.g. double|float, array|int[] -> float, int[]
        if ($redundantTypes = TypeComparator::getRedundantDocTypes($subject->getDocType())) {
            $subject->addDocTypeWarning(
                sprintf('Remove redundant :subject: type hints "%s"', TypeHelper::listRawTypes($redundantTypes))
            );
        }
    }

    public static function reportFakeTypedArrayTypes(AbstractTypeSubject $subject): void
    {
        if (!$subject->hasDefinedDocType()) {
            return;
        }

        // e.g. array[] -> mixed[][]
        if ($fakeType = TypeHelper::getFakeTypedArrayType($subject->getDocType())) {
            $subject->addDocTypeWarning(sprintf(
                'Use a more specific type in typed array hint "%s" for :subject:. Correct array depth must be specified.',
                $fakeType->toString()
            ));
        }
    }

    public static function reportIncompleteTypes(AbstractTypeSubject $subject): void
    {
        if (!$subject->hasDefinedDocType()) {
            return;
        }

        // e.g. @param null $arg1 -> @param int|null $arg1
        if ($subject->getDocType() instanceof NullType) {
            if ($subject instanceof ReturnTypeSubject) {
                $subject->addDocTypeWarning('Use void :subject :type declaration or change type to compound, e.g. SomeClass|null');
            } elseif ($subject instanceof ParamTypeSubject) {
                $subject->addDocTypeWarning('Change type hint for :subject: to compound, e.g. SomeClass|null');
            }
            // having @var null for const, prop is allowed
        }
    }

    public static function reportSuggestedTypes(AbstractTypeSubject $subject): void
    {
        if ($subject->hasDefinedDocType()) {
            return;
        }

        // e.g. ?int -> int|null
        $exampleDocType = TypeConverter::toExampleDocType($subject->getFnType());
        if (null !== $exampleDocType) {
            $subject->addDocTypeWarning(sprintf('Add type hint in PHPDoc tag for :subject:, e.g. "%s"', $exampleDocType->toString()));
        } else {
            $subject->addDocTypeWarning('Add type hint in PHPDoc tag for :subject:');
        }
    }

    public static function reportMissingOrWrongTypes(AbstractTypeSubject $subject): void
    {
        // e.g. $param1 = null, mixed|null -> do not report
        if ($subject instanceof ParamTypeSubject && !$subject->hasDefinedFnType()) {
            return;
        }

        // e.g. ?int, int|string -> ?int, int|null (wrong: string, missing: null)
        [$wrongDocTypes, $missingDocTypes] = TypeComparator::compare(
            $subject->getDocType(),
            $subject->getFnType(),
            $subject->getValueType()
        );

        if ($subject instanceof PropTypeSubject) {
            $wrongDocTypes = []; // not reported because props have dynamic values
        }

        // wrong types are not reported for dynamic assignments, e.g. class props.
        if ($wrongDocTypes) {
            $subject->addDocTypeWarning(sprintf(
                'Type %s "%s" %s not compatible with :subject: value type',
                isset($wrongDocTypes[1]) ? 'hints' : 'hint',
                TypeHelper::listRawTypes($wrongDocTypes),
                isset($wrongDocTypes[1]) ? 'are' : 'is'
            ));
        }

        if ($missingDocTypes) {
            $subject->addDocTypeWarning(sprintf(
                'Missing "%s" %s in :subject: type hint',
                TypeHelper::listRawTypes($missingDocTypes),
                isset($missingDocTypes[1]) ? 'types' : 'type'
            ));
        }
    }
}
