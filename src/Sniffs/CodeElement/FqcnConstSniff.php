<?php

namespace Gskema\TypeSniff\Sniffs\CodeElement;

use Gskema\TypeSniff\Inspection\DocTypeInspector;
use Gskema\TypeSniff\Inspection\Subject\ConstTypeSubject;
use PHP_CodeSniffer\Files\File;
use Gskema\TypeSniff\Core\CodeElement\Element\AbstractFqcnConstElement;
use Gskema\TypeSniff\Core\CodeElement\Element\ClassConstElement;
use Gskema\TypeSniff\Core\CodeElement\Element\CodeElementInterface;
use Gskema\TypeSniff\Core\CodeElement\Element\InterfaceConstElement;

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
        $subject = ConstTypeSubject::fromElement($const);

        if ($subject->hasDefinedDocBlock()) {
            DocTypeInspector::reportMissingTypedArrayTypes($subject);
            DocTypeInspector::reportFakeTypedArrayTypes($subject);
            DocTypeInspector::reportRedundantTypes($subject);
            DocTypeInspector::reportIncompleteTypes($subject);
            DocTypeInspector::reportMissingOrWrongTypes($subject);
        } else {
            DocTypeInspector::reportRequiredTypedArrayType($subject);
        }

        // @TODO Useless block?

        $subject->writeWarningsTo($file, static::CODE);
    }
}
