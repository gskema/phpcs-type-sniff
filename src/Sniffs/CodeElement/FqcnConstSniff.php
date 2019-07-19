<?php

namespace Gskema\TypeSniff\Sniffs\CodeElement;

use Gskema\TypeSniff\Core\Type\Common\UndefinedType;
use Gskema\TypeSniff\Core\Type\DocBlock\TypedArrayType;
use Gskema\TypeSniff\Core\Type\TypeComparator;
use Gskema\TypeSniff\Core\Type\TypeHelper;
use PHP_CodeSniffer\Files\File;
use Gskema\TypeSniff\Core\CodeElement\Element\AbstractFqcnConstElement;
use Gskema\TypeSniff\Core\CodeElement\Element\ClassConstElement;
use Gskema\TypeSniff\Core\CodeElement\Element\CodeElementInterface;
use Gskema\TypeSniff\Core\CodeElement\Element\InterfaceConstElement;
use Gskema\TypeSniff\Core\DocBlock\Tag\VarTag;
use Gskema\TypeSniff\Core\Type\Common\ArrayType;

class FqcnConstSniff implements CodeElementSniffInterface
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
            ClassConstElement::class,
            InterfaceConstElement::class,
        ];
    }

    /**
     * @inheritDoc
     *
     * @param AbstractFqcnConstElement $const
     */
    public function process(File $file, CodeElementInterface $const): void
    {
        $docBlock = $const->getDocBlock();

        /** @var VarTag|null $varTag */
        $varTag = $docBlock->getTagsByName('var')[0] ?? null;
        $docType = $varTag ? $varTag->getType() : null;
        $docTypeLine = $varTag ? $varTag->getLine() : $const->getLine();
        $valueType = $const->getValueType();

        $warnings = [];
        if (TypeHelper::containsType($docType, ArrayType::class)) {
            $warnings[$docTypeLine][] = 'Replace array type with typed array type in PHPDoc for :subject:. Use mixed[] for generic arrays. Correct array depth must be specified.';
        } elseif (is_a($valueType, ArrayType::class)
              && !TypeHelper::containsType($docType, TypedArrayType::class)
        ) {
            $warnings[$docTypeLine][] = 'Add PHPDoc with typed array type hint for :subject:. Use mixed[] for generic arrays. Correct array depth must be specified.';
        } elseif ($fakeType = TypeHelper::getFakeTypedArrayType($docType)) {
            $warnings[$docTypeLine][] = sprintf(
                'Use a more specific type in typed array hint "%s" for :subject:. Correct array depth must be specified.',
                $fakeType->toString()
            );
        }

        if ($docType && $valueType) {
            [$wrongDocTypes, $missingDocTypes] = TypeComparator::compare($docType, new UndefinedType(), $valueType);

            if ($wrongDocTypes) {
                $warnings[$docTypeLine][] = sprintf(
                    'Type %s "%s" %s not compatible with :subject: value type',
                    isset($wrongDocTypes[1]) ? 'hints' : 'hint',
                    TypeHelper::listRawTypes($wrongDocTypes),
                    isset($wrongDocTypes[1]) ? 'are' : 'is'
                );
            }

            if ($missingDocTypes) {
                $warnings[$docTypeLine][] = sprintf(
                    'Missing "%s" %s in :subject: type hint',
                    TypeHelper::listRawTypes($missingDocTypes),
                    isset($missingDocTypes[1]) ? 'types' : 'type'
                );
            }
        }

        $subject = $const->getConstName().' constant';
        foreach ($warnings as $line => $lineWarnings) {
            foreach ($lineWarnings as $warningTpl) {
                $warning = str_replace(':subject:', $subject, $warningTpl);
                $file->addWarningOnLine($warning, $line, 'FqcnConstSniff');
            }
        }
    }
}
