<?php

namespace Gskema\TypeSniff\Inspection;

use Gskema\TypeSniff\Core\Type\Common\ArrayType;
use Gskema\TypeSniff\Core\Type\Common\NullType;
use Gskema\TypeSniff\Core\Type\Common\UndefinedType;
use Gskema\TypeSniff\Core\Type\Common\VoidType;
use Gskema\TypeSniff\Core\Type\Declaration\NullableType;
use Gskema\TypeSniff\Core\Type\TypeComparator;
use Gskema\TypeSniff\Core\Type\TypeConverter;
use Gskema\TypeSniff\Core\Type\TypeHelper;
use Gskema\TypeSniff\Inspection\Subject\AbstractTypeSubject;
use Gskema\TypeSniff\Inspection\Subject\ParamTypeSubject;
use Gskema\TypeSniff\Inspection\Subject\PropTypeSubject;
use Gskema\TypeSniff\Inspection\Subject\ReturnTypeSubject;

class DocTypeInspector
{
    public static function reportMandatoryTypes(AbstractTypeSubject $subject, bool $allowMissing = false): void
    {
        $hasDocBlock = $subject->hasDefinedDocBlock();
        $hasDocTypeTag = null !== $subject->getDocType();
        $hasArrayShape = $subject->hasAttribute('ArrayShape');

        // e.g. $arg1 = [], C1 = [], $prop1 = [], ?array $arg1
        if (
            (!$hasDocTypeTag && !$hasArrayShape)
            && ($subject->getValueType() instanceof ArrayType || TypeHelper::containsType($subject->getFnType(), ArrayType::class))
        ) {
            $isNullable = $subject->getFnType() instanceof NullableType;
            $subject->addFnTypeWarning(sprintf(
                '%s typed array type hint for :subject:, .e.g.: "string[]%s" or "SomeClass[]%s". Correct array depth must be specified.',
                $subject->hasDefinedDocBlock() ? 'Add' : 'Create PHPDoc with',
                $isNullable ? '|null' : '',
                $isNullable ? '|null' : '',
            ));

            return; // exit
        }

        // Above: reports for doc types that must be specified (typed array type), cannot be missing.
        // Below: reports for missing doc types, tags. This may be OK in case fn type is specified.
        if ($allowMissing) {
            return;
        }

        if ($subject instanceof ParamTypeSubject) {
            // doc block must not be missing any @param tag
            if ($hasDocBlock && !$hasDocTypeTag && !$hasArrayShape) {
                $subject->addFnTypeWarning('Missing PHPDoc tag for :subject:');
            }
        } elseif ($subject instanceof PropTypeSubject) {
            // properties: ask to add fn type first
            if ($subject->getDocType() instanceof UndefinedType) {
                $subject->addDocTypeWarning('Add type hint to @var tag for :subject:');
            }
        }
    }

    public static function reportRemovableTypes(AbstractTypeSubject $subject): void
    {
        if (!$subject->hasDefinedDocType()) {
            return;
        }

        // @return void in not needed
        if (
            $subject instanceof ReturnTypeSubject
            && $subject->getFnType() instanceof VoidType
            && $subject->getDocType() instanceof VoidType
        ) {
            $subject->addDocTypeWarning('Remove @return void tag, not necessary');
        }

        // e.g. double|float, array|int[] -> float, int[]
        if ($redundantTypes = TypeComparator::getRedundantDocTypes($subject->getDocType())) {
            $subject->addDocTypeWarning(
                sprintf('Remove redundant :subject: type hints "%s"', TypeHelper::listRawTypes($redundantTypes))
            );
        }
    }

    public static function reportReplaceableTypes(AbstractTypeSubject $subject): void
    {
        if (!$subject->hasDefinedDocType()) {
            return;
        }

        $docType = $subject->getDocType();

        // e.g. @param array $arg1 -> @param int[] $arg1
        if (TypeHelper::containsType($docType, NullableType::class)) {
            $subject->addDocTypeWarning(sprintf(
                'Replace nullable type "%s" with union type with null "%s" for :subject:.',
                $docType->toString(),
                str_replace('?', '', $docType->toString()) . '|null', // @TODO Not ideal
            ));
        }

        // e.g. @param array $arg1 -> @param int[] $arg1
        if (TypeHelper::containsType($docType, ArrayType::class)) {
            $subject->addDocTypeWarning(
                'Replace array type with typed array type in PHPDoc for :subject:, .e.g.: "string[]" or "SomeClass[]". Use mixed[] for generic arrays. Correct array depth must be specified.',
            );
        }

        // e.g. array[] -> mixed[][]
        if ($fakeType = TypeHelper::getFakeTypedArrayType($docType)) {
            $subject->addDocTypeWarning(sprintf(
                'Use a more specific type in typed array hint "%s" for :subject:. Correct array depth must be specified.',
                $fakeType->toString(),
            ));
        }
    }

    public static function reportInvalidTypes(AbstractTypeSubject $subject): void
    {
        if (!$subject->hasDefinedDocType()) {
            return;
        }

        // @TODO true/void/false/$this/ cannot be param tags

        // e.g. @param null $arg1 -> @param int|null $arg1
        if ($subject->getDocType() instanceof NullType) {
            if ($subject instanceof ReturnTypeSubject) {
                $subject->addDocTypeWarning('Use void :subject: type declaration or change type to union, e.g. SomeClass|null');
            } elseif ($subject instanceof ParamTypeSubject) {
                $subject->addDocTypeWarning('Change type hint for :subject: to union, e.g. SomeClass|null');
            }
            // having @var null for const, prop is allowed
        }
    }

    public static function reportSuggestedTypes(AbstractTypeSubject $subject): void
    {
        if (!$subject->hasDefinedDocBlock() || $subject->hasDefinedDocType() || $subject->hasAttribute('ArrayShape')) {
            return;
        }

        // e.g. ?int -> int|null
        $exampleDocType = TypeConverter::toExampleDocType($subject->getFnType());
        if (null !== $exampleDocType) {
            $subject->addDocTypeWarning(sprintf('Add type hint in PHPDoc tag for :subject:, e.g. "%s"', $exampleDocType->toString()));
        } elseif ($subject instanceof ReturnTypeSubject) {
            if (!($subject->getFnType() instanceof VoidType)) {
                $subject->addFnTypeWarning('Missing PHPDoc tag or void type declaration for :subject:');
            }
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

        $hasArrayShape = $subject->hasAttribute('ArrayShape');
        if (
            $hasArrayShape
            && $subject->hasDefinedFnType()
            && !TypeHelper::containsType($subject->getFnType(), ArrayType::class)
        ) {
            $subject->addFnTypeWarning('Type declaration of :subject: not compatible with ArrayShape attribute');
        }

        if (!$subject->hasDefinedDocType()) {
            return;
        }

        // e.g. ?int, int|string -> ?int, int|null (wrong: string, missing: null)
        $isProp = $subject instanceof PropTypeSubject;
        [$wrongDocTypes, $missingDocTypes] = TypeComparator::compare(
            $subject->getDocType(),
            $subject->getFnType(),
            $subject->getValueType(),
            $isProp,
        );

        if ($isProp && !$subject->hasDefinedFnType()) {
            $wrongDocTypes = []; // not reported because props have dynamic values
        }

        // wrong types are not reported for dynamic assignments, e.g. class props.
        if ($wrongDocTypes) {
            $rawTypeList = TypeHelper::listRawTypes($wrongDocTypes);
            if ('$this' === $rawTypeList) {
                $subject->addDocTypeWarning(sprintf(
                    'Doc type "$this" for :subject: does not provide additional code intel and can be replaced with declared type "%s".',
                    $subject->getFnType()->toString()
                ));
            } else {
                $subject->addDocTypeWarning(sprintf(
                    'Type %s "%s" %s not compatible with :subject:%s type',
                    isset($wrongDocTypes[1]) ? 'hints' : 'hint',
                    $rawTypeList,
                    isset($wrongDocTypes[1]) ? 'are' : 'is',
                    $subject instanceof ReturnTypeSubject ? '' : ' value' // fixing messages
                ));
            }
        }

        if ($missingDocTypes) {
            $subject->addDocTypeWarning(sprintf(
                'Missing "%s" %s in :subject: type hint',
                TypeHelper::listRawTypes($missingDocTypes),
                isset($missingDocTypes[1]) ? 'types' : 'type',
            ));
        }
    }
}
