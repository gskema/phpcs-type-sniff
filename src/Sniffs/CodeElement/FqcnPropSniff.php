<?php

namespace Gskema\TypeSniff\Sniffs\CodeElement;

use Gskema\TypeSniff\Core\CodeElement\Element\AbstractFqcnElement;
use Gskema\TypeSniff\Core\DocBlock\Tag\VarTag;
use Gskema\TypeSniff\Core\Type\Declaration\NullableType;
use Gskema\TypeSniff\Inspection\DocTypeInspector;
use Gskema\TypeSniff\Inspection\FnTypeInspector;
use Gskema\TypeSniff\Inspection\Subject\PropTypeSubject;
use PHP_CodeSniffer\Files\File;
use Gskema\TypeSniff\Core\CodeElement\Element\AbstractFqcnPropElement;
use Gskema\TypeSniff\Core\CodeElement\Element\ClassPropElement;
use Gskema\TypeSniff\Core\CodeElement\Element\CodeElementInterface;
use Gskema\TypeSniff\Core\CodeElement\Element\TraitPropElement;

class FqcnPropSniff implements CodeElementSniffInterface
{
    protected const CODE = 'FqcnPropSniff';

    /** @var string */
    protected $reportType = 'warning';

    /** @var bool */
    protected $addViolationId = false;

    /**
     * @inheritDoc
     */
    public function configure(array $config): void
    {
        $this->reportType = $config['reportType'] ?? 'warning';
        $this->addViolationId = $config['addViolationId'] ?? false;
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
     *
     * @param AbstractFqcnPropElement $prop
     * @param AbstractFqcnElement     $parentElement
     */
    public function process(File $file, CodeElementInterface $prop, CodeElementInterface $parentElement): void
    {
        $subject = PropTypeSubject::fromElement($prop);

        FnTypeInspector::reportSuggestedTypes($subject);
        FnTypeInspector::reportReplaceableTypes($subject);

        DocTypeInspector::reportMandatoryTypes($subject);
        DocTypeInspector::reportReplaceableTypes($subject);
        DocTypeInspector::reportRemovableTypes($subject);
        DocTypeInspector::reportMissingOrWrongTypes($subject);

        static::reportInvalidDescription($subject);
        static::reportUselessPHPDoc($subject);

        $subject->writeViolationsTo($file, static::CODE, $this->reportType, $this->addViolationId);
    }

    protected static function reportInvalidDescription(PropTypeSubject $subject): void
    {
        /** @var VarTag|null $varTag */
        $varTag = $subject->getDocBlock()->getTagsByName('var')[0] ?? null;

        if ($varTag && null !== $varTag->getParamName()) {
            $subject->addDocTypeWarning('Remove property name $' . $varTag->getParamName() . ' from @var tag');
        }
    }

    protected static function reportUselessPHPDoc(PropTypeSubject $subject): void
    {
        if (!$subject->hasDefinedFnType() || !$subject->hasDefinedDocBlock()) {
            return; // nothing to do
        }

        $docBlock = $subject->getDocBlock();

        /** @var VarTag|null $varTag */
        $varTag = $docBlock->getTagsByName('var')[0] ?? null;

        $fnType = $subject->getFnType();
        $rawFnType = $fnType instanceof NullableType ? $fnType->toDocString() : $fnType->toString();
        $rawDocType = $subject->getDocType()->toString();

        $isUseful = $rawFnType !== $rawDocType
            || $docBlock->hasDescription()
            || ($varTag && $varTag->hasDescription())
            || array_diff($docBlock->getTagNames(), ['var']);

        if (!$isUseful) {
            $subject->addFnTypeWarning('Useless PHPDoc');
        }
    }
}
