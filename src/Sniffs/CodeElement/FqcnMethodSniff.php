<?php

namespace Gskema\TypeSniff\Sniffs\CodeElement;

use Gskema\TypeSniff\Inspection\FnTypeInspector;
use Gskema\TypeSniff\Inspection\DocTypeInspector;
use Gskema\TypeSniff\Inspection\Subject\AbstractTypeSubject;
use Gskema\TypeSniff\Inspection\Subject\ParamTypeSubject;
use Gskema\TypeSniff\Inspection\Subject\ReturnTypeSubject;
use PHP_CodeSniffer\Files\File;
use Gskema\TypeSniff\Core\CodeElement\Element\AbstractFqcnMethodElement;
use Gskema\TypeSniff\Core\CodeElement\Element\ClassMethodElement;
use Gskema\TypeSniff\Core\CodeElement\Element\CodeElementInterface;
use Gskema\TypeSniff\Core\CodeElement\Element\InterfaceMethodElement;
use Gskema\TypeSniff\Core\DocBlock\UndefinedDocBlock;
use Gskema\TypeSniff\Core\Type\Declaration\NullableType;

/**
 * @see FqcnMethodSniffTest
 */
class FqcnMethodSniff implements CodeElementSniffInterface
{
    protected const CODE = 'FqcnMethodSniff';

    /** @var string[] */
    protected $invalidTags = [];

    /** @var bool */
    protected $reportMissingTags = true;

    /**
     * @inheritDoc
     */
    public function configure(array $config): void
    {
        // TagInterface uses lowercase tags names, no @ symbol in front
        $invalidTags = [];
        foreach ($config['invalidTags'] ?? [] as $rawTag) {
            $invalidTags[] = strtolower(ltrim($rawTag, '@'));
        }
        $invalidTags = array_unique($invalidTags);

        $this->invalidTags = $invalidTags;
        $this->reportMissingTags = $config['reportMissingTags'] ?? true;
    }

    /**
     * @inheritDoc
     */
    public function register(): array
    {
        return [
            ClassMethodElement::class,
            // TraitMethodElement::class, // can be used to implement interface, not possible to know if it is extended
            InterfaceMethodElement::class,
        ];
    }

    /**
     * @inheritDoc
     * @param AbstractFqcnMethodElement $method
     */
    public function process(File $file, CodeElementInterface $method): void
    {
        $warningCountBefore = $file->getWarningCount();

        static::reportInvalidTags($file, $method, $this->invalidTags);
        $this->processMethod($file, $method);

        $hasNewWarnings = $file->getWarningCount() > $warningCountBefore;
        if (!$hasNewWarnings && $this->hasUselessDocBlock($method)) {
            $file->addWarningOnLine('Useless PHPDoc', $method->getLine(), static::CODE);
        }
    }

    protected function processMethod(File $file, AbstractFqcnMethodElement $method): void
    {
        $fnSig = $method->getSignature();
        $docBlock = $method->getDocBlock();
        $isMagicMethod = '__' === substr($fnSig->getName(), 0, 2);
        $isConstructMethod = '__construct' === $fnSig->getName();
        $hasInheritDocTag = $docBlock->hasTag('inheritdoc');

        // @inheritDoc
        // __construct can be detected as extended and magic, but we want to inspect it anyway
        if (!$isConstructMethod) {
            if ($hasInheritDocTag || $isMagicMethod) {
                return;
            } elseif ($method->isExtended()) {
                $file->addWarningOnLine('Missing @inheritDoc tag. Remove duplicated parent PHPDoc content.', $method->getLine(), static::CODE);
                return;
            }
        }

        // @param
        foreach ($fnSig->getParams() as $fnParam) {
            $paramTag = $docBlock->getParamTag($fnParam->getName());
            $subject = ParamTypeSubject::fromParam($fnParam, $paramTag, $docBlock);
            $this->processSigType($file, $subject);
        }

        // @return
        if (!$isConstructMethod) {
            $returnTag = $docBlock->getReturnTag();
            $subject = ReturnTypeSubject::fromSignature($fnSig, $returnTag, $docBlock);
            $this->processSigType($file, $subject);
        } else {
            foreach ($docBlock->getDescriptionLines() as $lineNum => $descLine) {
                if (preg_match('#^\w+\s+constructor\.?$#', $descLine)) {
                    $file->addWarningOnLine('Useless description.', $lineNum, static::CODE);
                }
            }
        }
    }

    protected function processSigType(File $file, AbstractTypeSubject $subject): void
    {
        FnTypeInspector::reportMandatoryTypes($subject);
        FnTypeInspector::reportSuggestedTypes($subject);
        FnTypeInspector::reportReplaceableTypes($subject);

        if ($this->reportMissingTags || $subject->hasDocTypeTag()) {
            DocTypeInspector::reportMandatoryTypes($subject);
            DocTypeInspector::reportSuggestedTypes($subject);
            DocTypeInspector::reportReplaceableTypes($subject);

            DocTypeInspector::reportRemovableTypes($subject);
            DocTypeInspector::reportInvalidTypes($subject);
            DocTypeInspector::reportMissingOrWrongTypes($subject);
        } else {
            DocTypeInspector::reportMandatoryTypes($subject, true);
        }

        $subject->writeWarningsTo($file, static::CODE);
    }

    protected function hasUselessDocBlock(AbstractFqcnMethodElement $method): bool
    {
        $fnSig = $method->getSignature();
        $docBlock = $method->getDocBlock();

        $docReturnTag = $docBlock->getReturnTag();

        if ($docBlock instanceof UndefinedDocBlock
            || $docBlock->hasDescription()
            || ($docReturnTag && $docReturnTag->hasDescription())
            // check if other "useful" tags are present
            || array_diff($docBlock->getTagNames(), ['param', 'return'])
        ) {
            return false;
        }

        foreach ($fnSig->getParams() as $fnParam) {
            $paramTag = $docBlock->getParamTag($fnParam->getName());
            if (null === $paramTag) {
                return false; // missing, needs to be fixed
            }

            if ($paramTag->hasDescription()) {
                return false;
            }

            $fnType = $fnParam->getType();
            $rawFnType = $fnType instanceof NullableType
                ? $fnType->toDocString()
                : $fnType->toString();
            if ($paramTag->getType()->toString() !== $rawFnType) {
                return false;
            }
        }

        $returnTag  = $docBlock->getReturnTag();
        $returnType = $fnSig->getReturnType();

        if ($returnTag && $returnType) {
            $rawReturnType = $returnType instanceof NullableType
                ? $returnType->toDocString()
                : $returnType->toString();
            if ($returnTag->getType()->toString() !== $rawReturnType) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param File                      $file
     * @param AbstractFqcnMethodElement $method
     * @param static[]                  $invalidTags
     */
    protected static function reportInvalidTags(File $file, AbstractFqcnMethodElement $method, array $invalidTags): void
    {
        foreach ($method->getDocBlock()->getTags() as $tag) {
            foreach ($invalidTags as $invalidTagName) {
                if ($tag->getName() === $invalidTagName) {
                    $file->addWarningOnLine('Useless tag', $tag->getLine(), static::CODE);
                }
            }
        }
    }
}
