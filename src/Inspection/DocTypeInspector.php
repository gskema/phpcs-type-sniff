<?php

namespace Gskema\TypeSniff\Inspection\Type;

use Gskema\TypeSniff\Core\Type\Common\ArrayType;
use Gskema\TypeSniff\Core\Type\Common\UndefinedType;
use Gskema\TypeSniff\Core\Type\Common\VoidType;
use Gskema\TypeSniff\Core\Type\DocBlock\NullType;
use Gskema\TypeSniff\Core\Type\DocBlock\TypedArrayType;
use Gskema\TypeSniff\Core\Type\TypeComparator;
use Gskema\TypeSniff\Core\Type\TypeConverter;
use Gskema\TypeSniff\Core\Type\TypeHelper;
use Gskema\TypeSniff\Core\Type\TypeInterface;
use Gskema\TypeSniff\Inspection\TypeSubject;

class DocTypeInspector
{
    public static function reportMissingTypedArrayTypes(TypeSubject $subject): void
    {
        // e.g. @param array $arg1 -> @param int[] $arg1
        $docHasTypedArray = TypeHelper::containsType($subject->getDocType(), TypedArrayType::class);
        $docHasArray = TypeHelper::containsType($subject->getDocType(), ArrayType::class);

        if (!$docHasTypedArray && $docHasArray) {
            $subject->addDocTypeWarning(
                'Replace array type with typed array type in PHPDoc for :subject:. Use mixed[] for generic arrays. Correct array depth must be specified.'
            );
        }
    }

    public static function reportRequiredDocBlock(TypeSubject $subject): void
    {
        // e.g. func1(array $arg1) -> must have DocBlock with TypedArrayType
        if ($subject->getFnType() instanceof UndefinedType) {
            $subject->addFnTypeWarning('Add type declaration for :subject: or create PHPDoc with type hint');
        } elseif (TypeHelper::containsType($subject->getFnType(), ArrayType::class)) {
            $subject->addFnTypeWarning('Create PHPDoc with typed array type hint for :subject:, .e.g.: "string[]" or "SomeClass[]"');
        }
    }

    public static function reportMissingTags(TypeSubject $subject): void
    {
        // e.g. DocBlock exists, but no @param tag
        if ($subject->isReturnType()) {
            if (!($subject->getFnType() instanceof VoidType)) {
                $subject->addFnTypeWarning('Missing PHPDoc tag or void type declaration for :subject:');
            }
        } else {
            $subject->addFnTypeWarning('Missing PHPDoc tag for :subject:');
        }
    }

    public static function reportUnnecessaryTags(TypeSubject $subject): void
    {
        // @return void in not needed
        if ($subject->isReturnType()
            && $subject->getFnType() instanceof VoidType
            && $subject->getDocType() instanceof VoidType
        ) {
            $subject->addDocTypeWarning('Remove @return void tag, not necessary');
        }
    }

    public static function reportRedundantTypes(TypeSubject $subject): void
    {
        // e.g. double|float, array|int[] -> float, int[]
        if ($redundantTypes = TypeComparator::getRedundantDocTypes($subject->getDocType())) {
            $subject->addDocTypeWarning(
                sprintf('Remove redundant :subject: type hints "%s"', TypeHelper::listRawTypes($redundantTypes))
            );
        }
    }

    public static function reportFakeTypedArrayTypes(TypeSubject $subject): void
    {
        // e.g. array[] -> mixed[][]
        if ($fakeType = TypeHelper::getFakeTypedArrayType($subject->getDocType())) {
            $subject->addDocTypeWarning(sprintf(
                'Use a more specific type in typed array hint "%s" for :subject:. Correct array depth must be specified.',
                $fakeType->toString()
            ));
        }
    }

    public static function reportIncompleteTypes(TypeSubject $subject): void
    {
        // e.g. @param null $arg1 -> @param int|null $arg1
        if ($subject->getDocType() instanceof NullType) {
            if ($subject->isReturnType()) {
                $subject->addDocTypeWarning('Use void :subject :type declaration or change type to compound, e.g. SomeClass|null');
            } else {
                $subject->addDocTypeWarning('Change type hint for :subject: to compound, e.g. SomeClass|null');
            }
        }
    }

    public static function reportSuggestedTypes(TypeSubject $subject): void
    {
        // e.g. ?int -> int|null
        $exampleDocType = TypeConverter::toExampleDocType($subject->getFnType());
        if (null !== $exampleDocType) {
            $subject->addDocTypeWarning(sprintf('Add type hint in PHPDoc tag for :subject:, e.g. "%s"', $exampleDocType->toString()));
        } else {
            $subject->addDocTypeWarning('Add type hint in PHPDoc tag for :subject:');
        }
    }

    public static function reportMissingOrWrongTypes(TypeSubject $subject, bool $dynamicAssignment): void
    {
        // e.g. ?int, int|string -> ?int, int|null (wrong: string, missing: null)
        if ($subject->hasDefinedDocType() && $subject->hasDefinedFnType()) {
            /** @var TypeInterface[] $wrongDocTypes */
            /** @var TypeInterface[] $missingDocTypes */
            [$wrongDocTypes, $missingDocTypes] = TypeComparator::compare(
                $subject->getDocType(),
                $subject->getFnType(),
                $subject->getValueType()
            );

            if (!$dynamicAssignment && $wrongDocTypes) {
                $subject->addDocTypeWarning(sprintf(
                    'Type %s "%s" %s not compatible with :subject: type declaration',
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
}
