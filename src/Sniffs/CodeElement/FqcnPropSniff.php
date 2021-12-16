<?php

namespace Gskema\TypeSniff\Sniffs\CodeElement;

use Gskema\TypeSniff\Core\CodeElement\Element\AbstractFqcnElement;
use Gskema\TypeSniff\Core\CodeElement\Element\ClassElement;
use Gskema\TypeSniff\Core\DocBlock\Tag\VarTag;
use Gskema\TypeSniff\Core\ReflectionCache;
use Gskema\TypeSniff\Core\Type\Declaration\NullableType;
use Gskema\TypeSniff\Inspection\DocTypeInspector;
use Gskema\TypeSniff\Inspection\FnTypeInspector;
use Gskema\TypeSniff\Inspection\Subject\PropTypeSubject;
use PHP_CodeSniffer\Files\File;
use Gskema\TypeSniff\Core\CodeElement\Element\AbstractFqcnPropElement;
use Gskema\TypeSniff\Core\CodeElement\Element\ClassPropElement;
use Gskema\TypeSniff\Core\CodeElement\Element\CodeElementInterface;
use Gskema\TypeSniff\Core\CodeElement\Element\TraitPropElement;
use Throwable;

class FqcnPropSniff implements CodeElementSniffInterface
{
    protected const CODE = 'FqcnPropSniff';

    protected string $reportType = 'warning';

    protected bool $addViolationId = false;

    /**
     * @inheritDoc
     */
    public function configure(array $config): void
    {
        $this->reportType = (string)($config['reportType'] ?? 'warning');
        $this->addViolationId = (bool)($config['addViolationId'] ?? false);
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

        // Do not report required fn type if prop is extended. To do this, reflection is needed.
        // If prop has fn type or class is not extended, then there is no point in checking for parent props, skip.
        $skipRequireFnType = false;
        if (
            !$subject->hasDefinedFnType()
            && $parentElement instanceof ClassElement
            && $parentElement->isExtended()
        ) {
            // Extended class = prop may be extended.
            try {
                $parentPropNames = ReflectionCache::getPropsRecursive($parentElement->getFqcn(), false);
                $skipRequireFnType = in_array($prop->getPropName(), $parentPropNames); // is prop extended?
            } catch (Throwable $e) {
                $skipRequireFnType = true; // most likely parent class not found, don't report
            }
        }

        if (!$skipRequireFnType) {
            FnTypeInspector::reportSuggestedTypes($subject);
        }
        // else: if prop is extended and doesn't have fn type, PHP will crash.

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
        // @var tag might be missing
        $rawDocType = $subject->getDocType() ? $subject->getDocType()->toString() : null;

        $isUseful = ($rawDocType && $rawFnType !== $rawDocType)
            || $docBlock->hasDescription()
            || ($varTag && $varTag->hasDescription())
            || array_diff($docBlock->getTagNames(), ['var']);

        if (!$isUseful) {
            $subject->addFnTypeWarning('Useless PHPDoc');
        }
    }
}
