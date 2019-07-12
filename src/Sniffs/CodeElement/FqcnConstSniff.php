<?php

namespace Gskema\TypeSniff\Sniffs\CodeElement;

use Gskema\TypeSniff\Core\Type\DocBlock\CompoundType;
use Gskema\TypeSniff\Core\Type\DocBlock\TypedArrayType;
use Gskema\TypeSniff\Core\Type\TypeInterface;
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
        // @TODO Infer type from value?
        $docBlock = $const->getDocBlock();

        /** @var VarTag|null $varTag */
        $varTag = $docBlock->getTagsByName('var')[0] ?? null;
        $docType = $varTag ? $varTag->getType() : null;

        $subject = $const->getConstName().' constant';

        if ($this->containsType($docType, ArrayType::class)) {
            $file->addWarningOnLine(
                'Replace array type with typed array type in PHPDoc for '.$subject.'. Use mixed[] for generic arrays.',
                $const->getLine(),
                'FqcnConstSniff'
            );
        } elseif (is_a($const->getValueType(), ArrayType::class)
              && !$this->containsType($docType, TypedArrayType::class)
        ) {
            $file->addWarningOnLine(
                'Add PHPDoc with typed array type hint for '.$subject.'. Use mixed[] for generic arrays.',
                $const->getLine(),
                'FqcnConstSniff'
            );
        }
    }


    protected function containsType(?TypeInterface $type, string $typeClassName): bool
    {
        return is_a($type, $typeClassName)
            || ($type instanceof CompoundType && $type->containsType($typeClassName));
    }
}
