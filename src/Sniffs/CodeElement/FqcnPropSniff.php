<?php

namespace Gskema\TypeSniff\Sniffs\CodeElement;

use Gskema\TypeSniff\Core\Type\DocBlock\TypedArrayType;
use Gskema\TypeSniff\Core\Type\TypeComparator;
use Gskema\TypeSniff\Core\Type\TypeHelper;
use PHP_CodeSniffer\Files\File;
use Gskema\TypeSniff\Core\CodeElement\Element\AbstractFqcnPropElement;
use Gskema\TypeSniff\Core\CodeElement\Element\ClassPropElement;
use Gskema\TypeSniff\Core\CodeElement\Element\CodeElementInterface;
use Gskema\TypeSniff\Core\CodeElement\Element\TraitPropElement;
use Gskema\TypeSniff\Core\DocBlock\Tag\VarTag;
use Gskema\TypeSniff\Core\DocBlock\UndefinedDocBlock;
use Gskema\TypeSniff\Core\Type\Common\ArrayType;
use Gskema\TypeSniff\Core\Type\Common\UndefinedType;

class FqcnPropSniff implements CodeElementSniffInterface
{
    /**
     * @inheritDoc
     */
    public function configure(array $config): void
    {
        // nothing to do
    }

    /**
     * @inheritDoc
     */
    public function register(): array
    {
        return [
            ClassPropElement::class,
            TraitPropElement::class,
        ];
    }

    /**
     * @inheritDoc
     * @param AbstractFqcnPropElement $prop
     */
    public function process(File $file, CodeElementInterface $prop): void
    {
        $docBlock = $prop->getDocBlock();
        $defValueType = $prop->getDefaultValueType();

        /** @var VarTag|null $varTag */
        $varTag = $docBlock->getTagsByName('var')[0] ?? null;
        $docType = $varTag ? $varTag->getType() : null;
        $docTypeLine = $varTag ? $varTag->getLine() : $prop->getLine();
        $fnTypeLine = $prop->getLine();

        $warnings = [];
        if ($docBlock instanceof UndefinedDocBlock) {
            $warnings[$fnTypeLine][] = 'Add PHPDoc for :subject:';
        } elseif (null === $varTag) {
            $warnings[$docTypeLine][] = 'Add @var tag for :subject:';
        } elseif ($docType instanceof UndefinedType) {
            $warnings[$docTypeLine][] = 'Add type hint to @var tag for :subject:';
        } elseif (TypeHelper::containsType($docType, ArrayType::class)) {
            $warnings[$docTypeLine][] = 'Replace array type with typed array type in PHPDoc for :subject:. Use mixed[] for generic arrays. Correct array depth must be specified.';
        } elseif (is_a($defValueType, ArrayType::class)
            && !TypeHelper::containsType($docType, TypedArrayType::class)
        ) {
            $warnings[$docTypeLine][] = 'Add PHPDoc with typed array type hint for :subject:. Use mixed[] for generic arrays. Correct array depth must be specified.';
        } elseif ($fakeType = TypeHelper::getFakeTypedArrayType($docType)) {
            $warnings[$docTypeLine][] = sprintf(
                'Use a more specific type in typed array hint "%s" for :subject:. Correct array depth must be specified.',
                $fakeType->toString()
            );
        }

        if ($varTag && null !== $varTag->getParamName()) {
            $warnings[$docTypeLine][] = 'Remove property name $'.$varTag->getParamName().' from @var tag';
        }

        if ($docType && $defValueType) {
            // $wrongTypes are intentionally ignored, props are dynamic
            [, $missingDocTypes] = TypeComparator::compare($docType, new UndefinedType(), $defValueType);

            if ($missingDocTypes) {
                $warnings[$docTypeLine][] = sprintf(
                    'Missing "%s" %s in :subject: type hint',
                    TypeHelper::listRawTypes($missingDocTypes),
                    isset($missingDocTypes[1]) ? 'types' : 'type'
                );
            }
        }

        $subject = 'property $'.$prop->getPropName();
        foreach ($warnings as $line => $lineWarnings) {
            foreach ($lineWarnings as $warningTpl) {
                $warning = str_replace(':subject:', $subject, $warningTpl);
                $file->addWarningOnLine($warning, $line, 'FqcnPropSniff');
            }
        }
    }
}
