<?php

namespace Gskema\TypeSniff\Sniffs\CodeElement;

use Gskema\TypeSniff\Core\Type\Common\UndefinedType;
use Gskema\TypeSniff\Inspection\DocTypeInspector;
use Gskema\TypeSniff\Inspection\TypeSubject;
use PHP_CodeSniffer\Files\File;
use Gskema\TypeSniff\Core\CodeElement\Element\AbstractFqcnConstElement;
use Gskema\TypeSniff\Core\CodeElement\Element\ClassConstElement;
use Gskema\TypeSniff\Core\CodeElement\Element\CodeElementInterface;
use Gskema\TypeSniff\Core\CodeElement\Element\InterfaceConstElement;
use Gskema\TypeSniff\Core\DocBlock\Tag\VarTag;

class FqcnConstSniff implements CodeElementSniffInterface
{
    protected const CODE = 'FqcnConstSniff';

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

        $subject = new TypeSubject(
            $varTag ? $varTag->getType() : null,
            new UndefinedType(),
            $const->getValueType(),
            $varTag ? $varTag->getLine() : null,
            $const->getLine(),
            $const->getConstName().' constant',
            false,
            $docBlock
        );

        if ($subject->hasDefinedDocBlock()) {
            DocTypeInspector::reportMissingTypedArrayTypes($subject);
            DocTypeInspector::reportFakeTypedArrayTypes($subject);
            DocTypeInspector::reportRedundantTypes($subject);
            DocTypeInspector::reportIncompleteTypes($subject);
            DocTypeInspector::reportMissingOrWrongTypes($subject, false);
        } else {
            DocTypeInspector::reportRequiredTypedArrayType($subject);
        }

        // @TODO Useless block?

        $subject->writeWarningsTo($file, static::CODE);
    }
}
