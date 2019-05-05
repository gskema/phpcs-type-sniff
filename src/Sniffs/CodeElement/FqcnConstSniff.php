<?php

namespace Gskema\TypeSniff\Sniffs\CodeElement;

use PHP_CodeSniffer\Files\File;
use Gskema\TypeSniff\Core\CodeElement\Element\AbstractFqcnConstElement;
use Gskema\TypeSniff\Core\CodeElement\Element\ClassConstElement;
use Gskema\TypeSniff\Core\CodeElement\Element\CodeElementInterface;
use Gskema\TypeSniff\Core\CodeElement\Element\InterfaceConstElement;
use Gskema\TypeSniff\Core\DocBlock\Tag\VarTag;
use Gskema\TypeSniff\Core\DocBlock\UndefinedDocBlock;
use Gskema\TypeSniff\Core\Type\Common\ArrayType;

class FqcnConstSniff implements CodeElementSniffInterface
{
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
        // @TODO Infer type from value?
        $docBlock = $const->getDocBlock();

        /** @var VarTag|null $varTag */
        $varTag = $docBlock->getTagsByName('var')[0] ?? null;
        $varType = $varTag ? $varTag->getType() : null;

        // PHPDoc for const are usually not necessary.
        if ($docBlock instanceof UndefinedDocBlock) {
            return;
        }

        if ($varType instanceof ArrayType) {
            $subject = $const->getConstName().' constant';
            $file->addWarningOnLine(
                'Replace array type with typed array type in PHPDoc for '.$subject.'. Use mixed[] for generic arrays.',
                $const->getLine(),
                'FqcnPropSniff'
            );
        }

        if ($varTag && null !== $varTag->getParamName()) {
            $file->addWarningOnLine(
                'Remove property name $'.$varTag->getParamName().' from @var tag',
                $const->getLine(),
                'FqcnPropSniff'
            );
        }
    }
}
