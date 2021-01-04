<?php

namespace Gskema\TypeSniff\Sniffs\CodeElement;

use Gskema\TypeSniff\Core\CodeElement\Element\AbstractFqcnElement;
use Gskema\TypeSniff\Core\CodeElement\Element\AbstractFqcnMethodElement;
use Gskema\TypeSniff\Core\CodeElement\Element\ClassElement;
use Gskema\TypeSniff\Core\CodeElement\Element\ClassMethodElement;
use Gskema\TypeSniff\Core\CodeElement\Element\TraitElement;
use Gskema\TypeSniff\Core\CodeElement\Element\TraitMethodElement;
use Gskema\TypeSniff\Core\DocBlock\Tag\VarTag;
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

    /** @var string */
    protected $reportType = 'warning';

    /**
     * @inheritDoc
     */
    public function configure(array $config): void
    {
        $this->reportUninitializedProp = $config['reportUninitializedProp'] ?? true;
        $this->reportType = $config['reportType'] ?? 'warning';
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

        $subject->writeViolationsTo($file, static::CODE, $this->reportType);
    }

    protected static function reportInvalidDescription(PropTypeSubject $subject): void
    {
        /** @var VarTag|null $varTag */
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

        $propHasDefaultValue = $prop->getMetadata()->hasDefaultValue(); // null = not detected

        $ownConstructor = $parent->getOwnConstructor();

        if ((false === $propHasDefaultValue || $prop->getDefaultValueType() instanceof NullType)
            && !TypeHelper::containsType($subject->getDocType(), NullType::class)
            && (!$ownConstructor || !static::hasNonNullAssignedProp($parent, $ownConstructor, $prop->getPropName()))
        ) {
            $subject->addDocTypeWarning(':Subject: not initialized by __construct(), add null doc type or set a default value');
        }
    }

    /**
     * @param ClassElement|TraitElement|AbstractFqcnElement                   $parent
     * @param ClassMethodElement|TraitMethodElement|AbstractFqcnMethodElement $method
     * @param string                                                          $propName
     *
     * @return bool|null
     */
    public static function hasNonNullAssignedProp(
        AbstractFqcnElement $parent,
        AbstractFqcnMethodElement $method,
        string $propName
    ): ?bool {
        // Not ideal because we have to descend on every prop inspection.
        // However early exit is used, so we don't have to descend fully each time.
        $visitedNames = [];
        $unvisitedNames = [$method->getSignature()->getName()];
        while (!empty($unvisitedNames)) {
            $callNames = [];
            foreach ($unvisitedNames as $unvisitedName) {
                $unvisitedMethod = $parent->getMethod($unvisitedName);
                if (null === $unvisitedMethod) {
                    continue;
                }
                $nonNullAssignedProps = $unvisitedMethod->getMetadata()->getNonNullAssignedProps() ?? []; // never null
                if (in_array($propName, $nonNullAssignedProps)) {
                    return true;
                }
                $callNameChunk = $unvisitedMethod->getMetadata()->getThisMethodCalls() ?? []; // never null
                $callNames = array_merge($callNames, $callNameChunk);
            }
            $visitedNames = array_merge($visitedNames, $unvisitedNames);
            $unvisitedNames = array_diff($callNames, $visitedNames);
        }

        return false;
    }
}
