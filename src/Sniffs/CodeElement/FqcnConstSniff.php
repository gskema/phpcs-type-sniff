<?php

namespace Gskema\TypeSniff\Sniffs\CodeElement;

use Gskema\TypeSniff\Core\DocBlock\Tag\VarTag;
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

        DocTypeInspector::reportMandatoryTypes($subject);
        DocTypeInspector::reportReplaceableTypes($subject);

        DocTypeInspector::reportRemovableTypes($subject);
        DocTypeInspector::reportMissingOrWrongTypes($subject);

        static::reportUselessDocBlock($subject);

        $subject->writeWarningsTo($file, static::CODE);
    }

    protected static function reportUselessDocBlock(ConstTypeSubject $subject): void
    {
        if (!$subject->hasDefinedDocBlock()) {
            return;
        }

        $docBlock = $subject->getDocBlock();

        /** @var VarTag|null $varTag */
        $varTag = $docBlock->getTagsByName('var')[0] ?? null;

        $tagCount = count($docBlock->getTags());
        $hasOtherTags = (!$varTag && $tagCount >= 1) || ($varTag && $tagCount >= 2);

        $isUseful = $hasOtherTags
            || $docBlock->hasDescription()
            || ($varTag && $varTag->hasDescription())
            || ($varTag && $varTag->getType() != $subject->getValueType()); // intentional non strict

        if (!$isUseful) {
            $subject->addFnTypeWarning('Useless PHPDoc');
        }
    }
}
