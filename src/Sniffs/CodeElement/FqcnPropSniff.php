<?php

namespace Gskema\TypeSniff\Sniffs\CodeElement;

use Gskema\TypeSniff\Inspection\DocTypeInspector;
use Gskema\TypeSniff\Inspection\Subject\PropTypeSubject;
use PHP_CodeSniffer\Files\File;
use Gskema\TypeSniff\Core\CodeElement\Element\AbstractFqcnPropElement;
use Gskema\TypeSniff\Core\CodeElement\Element\ClassPropElement;
use Gskema\TypeSniff\Core\CodeElement\Element\CodeElementInterface;
use Gskema\TypeSniff\Core\CodeElement\Element\TraitPropElement;
use Gskema\TypeSniff\Core\DocBlock\Tag\VarTag;
use Gskema\TypeSniff\Core\DocBlock\UndefinedDocBlock;
use Gskema\TypeSniff\Core\Type\Common\UndefinedType;

class FqcnPropSniff implements CodeElementSniffInterface
{
    protected const CODE = 'FqcnPropSniff';

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

        /** @var VarTag|null $varTag */
        $varTag = $docBlock->getTagsByName('var')[0] ?? null;
        $docType = $varTag ? $varTag->getType() : null;

        $subject = PropTypeSubject::fromElement($prop);

        if ($docBlock instanceof UndefinedDocBlock) {
            $subject->addDocTypeWarning('Add PHPDoc for :subject:');
        } elseif (null === $varTag) {
            $subject->addDocTypeWarning('Add @var tag for :subject:');
        } elseif ($docType instanceof UndefinedType) {
            $subject->addDocTypeWarning('Add type hint to @var tag for :subject:');
        }

        if ($varTag && null !== $varTag->getParamName()) {
            $subject->addDocTypeWarning('Remove property name $'.$varTag->getParamName().' from @var tag');
        }

        if ($subject->hasDefinedDocType()) {
            DocTypeInspector::reportMissingTypedArrayTypes($subject);
            DocTypeInspector::reportFakeTypedArrayTypes($subject);
            DocTypeInspector::reportRedundantTypes($subject);
            DocTypeInspector::reportIncompleteTypes($subject);
            DocTypeInspector::reportMissingOrWrongTypes($subject, true);
        } else {
            DocTypeInspector::reportRequiredTypedArrayType($subject);
        }

        $subject->writeWarningsTo($file, static::CODE);
    }
}
