<?php

namespace Gskema\TypeSniff\Sniffs\CodeElement;

use Gskema\TypeSniff\Core\CodeElement\Element\AbstractFqcnElement;
use Gskema\TypeSniff\Core\CodeElement\Element\ClassElement;
use Gskema\TypeSniff\Core\CodeElement\Element\TraitElement;
use Gskema\TypeSniff\Core\Type\DocBlock\NullType;
use Gskema\TypeSniff\Core\Type\TypeHelper;
use Gskema\TypeSniff\Inspection\DocTypeInspector;
use Gskema\TypeSniff\Inspection\Subject\PropTypeSubject;
use PHP_CodeSniffer\Files\File;
use Gskema\TypeSniff\Core\CodeElement\Element\AbstractFqcnPropElement;
use Gskema\TypeSniff\Core\CodeElement\Element\ClassPropElement;
use Gskema\TypeSniff\Core\CodeElement\Element\CodeElementInterface;
use Gskema\TypeSniff\Core\CodeElement\Element\TraitPropElement;

class FqcnPropSniff implements CodeElementSniffInterface
{
    protected const CODE = 'FqcnPropSniff';

    /** @var bool */
    protected $reportUninitializedProp = true;

    /**
     * @inheritDoc
     */
    public function configure(array $config): void
    {
        $this->reportUninitializedProp = $config['reportUninitializedProp'] ?? true;
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
     * @param AbstractFqcnElement $parentElement
     */
    public function process(File $file, CodeElementInterface $prop, CodeElementInterface $parentElement): void
    {
        $subject = PropTypeSubject::fromElement($prop);

        DocTypeInspector::reportMandatoryTypes($subject);
        DocTypeInspector::reportReplaceableTypes($subject);
        DocTypeInspector::reportRemovableTypes($subject);
        DocTypeInspector::reportMissingOrWrongTypes($subject);

        static::reportInvalidDescription($subject);
        $this->reportUninitializedProp && static::reportUninitializedProp($subject, $prop, $parentElement);

        $subject->writeWarningsTo($file, static::CODE);
    }

    protected static function reportInvalidDescription(PropTypeSubject $subject): void
    {
        $varTag = $subject->getDocBlock()->getTagsByName('var')[0] ?? null;

        if ($varTag && null !== $varTag->getParamName()) {
            $subject->addDocTypeWarning('Remove property name $'.$varTag->getParamName().' from @var tag');
        }
    }

    /**
     * @param PropTypeSubject                                           $subject
     * @param AbstractFqcnPropElement|ClassPropElement|TraitPropElement $prop
     * @param AbstractFqcnElement|ClassElement|TraitElement             $parent
     */
    protected static function reportUninitializedProp(
        PropTypeSubject $subject,
        AbstractFqcnPropElement $prop,
        AbstractFqcnElement $parent
    ): void {
        // Report nothing for PHP7.4 instead of reporting wrong
        if (version_compare(PHP_VERSION, '7.4', '>=')) {
            return; // @TODO Exit when prop has defined fnType
        }

        $ownConstructor = $parent->getOwnConstructor();

        $nonNullAssignedProps = [];
        if (null !== $ownConstructor) {
            $nonNullAssignedProps = $ownConstructor->getMetadata()->getNonNullAssignedProps();
            if (null === $nonNullAssignedProps) {
                return;  // null = not detected, cannot assume anything on missing information.
            }
        }
        $propHasDefaultValue = $prop->getMetadata()->hasDefaultValue(); // null = not detected

        if ((false === $propHasDefaultValue || $prop->getDefaultValueType() instanceof NullType)
            && !TypeHelper::containsType($subject->getDocType(), NullType::class)
            && !in_array($prop->getPropName(), $nonNullAssignedProps)
        ) {
            $subject->addDocTypeWarning(':Subject: not initialized by __construct(), add null doc type or set a default value');
        }
    }
}
