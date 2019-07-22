<?php

namespace Gskema\TypeSniff\Sniffs\CodeElement;

use Gskema\TypeSniff\Core\Type\TypeComparator;
use Gskema\TypeSniff\Core\Type\TypeHelper;
use Gskema\TypeSniff\Inspection\FnTypeInspector;
use Gskema\TypeSniff\Inspection\Type\DocTypeInspector;
use Gskema\TypeSniff\Inspection\TypeSubject;
use PHP_CodeSniffer\Files\File;
use Gskema\TypeSniff\Core\CodeElement\Element\AbstractFqcnMethodElement;
use Gskema\TypeSniff\Core\CodeElement\Element\ClassMethodElement;
use Gskema\TypeSniff\Core\CodeElement\Element\CodeElementInterface;
use Gskema\TypeSniff\Core\CodeElement\Element\InterfaceMethodElement;
use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\DocBlock\UndefinedDocBlock;
use Gskema\TypeSniff\Core\Type\Common\ArrayType;
use Gskema\TypeSniff\Core\Type\Common\UndefinedType;
use Gskema\TypeSniff\Core\Type\Common\VoidType;
use Gskema\TypeSniff\Core\Type\Declaration\NullableType;
use Gskema\TypeSniff\Core\Type\DocBlock\NullType;
use Gskema\TypeSniff\Core\Type\DocBlock\TypedArrayType;
use Gskema\TypeSniff\Core\Type\TypeConverter;
use Gskema\TypeSniff\Core\Type\TypeInterface;

class FqcnMethodSniff implements CodeElementSniffInterface
{
    protected const CODE = 'FqcnMethodSniff';

    /** @var string[] */
    protected $baseUsefulTags = [
        '@deprecated',
        '@throws',
        '@dataProvider',
        '@see',
        '@todo',
        '@inheritDoc'
    ];

    /** @var string[] */
    protected $usefulTags = [];

    /**
     * @inheritDoc
     */
    public function configure(array $config): void
    {
        $rawTags = array_merge($this->baseUsefulTags, $config['usefulTags'] ?? []);

        $usefulTags = [];
        foreach ($rawTags as $rawTag) {
            $usefulTags[] = strtolower(ltrim($rawTag, '@'));
        }
        $usefulTags = array_unique($usefulTags);

        $this->usefulTags = $usefulTags;
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

        // @TODO Assert description
        $this->processMethod($file, $method);

        $hasNewWarnings = $file->getWarningCount() > $warningCountBefore;
        if (!$hasNewWarnings && $this->hasUselessDocBlock($method)) {
            $file->addWarningOnLine('Useless PHPDoc', $method->getLine(), 'FqcnMethodSniff');
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
                $file->addWarningOnLine('Missing @inheritDoc tag. Remove duplicated parent PHPDoc content.', $method->getLine(), 'FqcnMethodSniff');
                return;
            }
        }

        // @param
        foreach ($fnSig->getParams() as $fnParam) {

            $subject = new TypeSubject(
                $docType = $tag ? $tag->getType() : null,
                $fnType = $fnParam->getType(),
                $valueType = $fnParam->getValueType(),
                $docTypeLine = $tag ? $tag->getLine() : $fnTypeLine,
                $fnTypeLine = $fnParam->getLine(),
                sprintf('parameter $%s', $fnParam->getName()),
                false
            );

            $tag = $docBlock->getParamTag($paramName);






            $this->processSigType($file, $docBlock, $subject, $fnType, $fnTypeLine, $docType, $docTypeLine, $valueType);
        }

        // @return
        if (!$isConstructMethod) {
            $returnTag = $docBlock->getReturnTag();
            $this->processSigType(
                $file,
                $docBlock,
                'return value',
                $fnSig->getReturnType(),
                $fnSig->getReturnLine(),
                $returnTag ? $returnTag->getType() : null,
                $returnTag ? $returnTag->getLine() : $fnSig->getLine(),
                new UndefinedType()
            );
        } else {
            foreach ($docBlock->getDescriptionLines() as $lineNum => $descLine) {
                if (preg_match('#^\w+\s+constructor\.?$#', $descLine)) {
                    $file->addWarningOnLine('Useless description.', $lineNum, 'FqcnMethodSniff');
                }
            }
        }
    }

    protected function processSigType(
        File $file,
        DocBlock $docBlock,
        string $subject,
        TypeInterface $fnType,
        int $fnTypeLine,
        ?TypeInterface $docType,
        int $docTypeLine,
        ?TypeInterface $valueType
    ): void {
        $subject = new TypeSubject(
            $docType,
            $fnType,
            $valueType,
            $docTypeLine,
            $fnTypeLine,
            $subject,
            'return value' === $subject
        );

        $isReturnType = 'return value' === $subject;
        $docTypeDefined = !($docType instanceof UndefinedType);
        $fnTypeDefined = !($fnType instanceof UndefinedType);

        /** @var string[][] $warnings */
        $warnings = [];

        FnTypeInspector::reportExpectedNullableType($subject);

        if ($docBlock instanceof UndefinedDocBlock) {
            DocTypeInspector::reportRequiredDocBlock($subject);
        } elseif (null === $docType) {
            DocTypeInspector::reportMissingTags($subject);
        } else {
            if ($docTypeDefined) {
                // Require typed array type
                // Require composite with null instead of null
                // @TODO true/void/false/$this/ cannot be param tags

                DocTypeInspector::reportMissingTypedArrayTypes($subject);
                DocTypeInspector::reportRedundantTypes($subject);
                DocTypeInspector::reportIncompleteTypes($subject);
            } else {
                DocTypeInspector::reportSuggestedTypes($subject);
            }

            if (!$fnTypeDefined) {
                FnTypeInspector::reportSuggestedFnTypes($subject);
            }

            if ($docTypeDefined && $fnTypeDefined) {
                DocTypeInspector::reportUnnecessaryTags($subject);
                DocTypeInspector::reportMissingOrWrongTypes($subject, false);
            }
        }

        $subject->passWarningsTo($file, static::CODE);
    }

    protected function hasUselessDocBlock(AbstractFqcnMethodElement $method): bool
    {
        $fnSig = $method->getSignature();
        $docBlock = $method->getDocBlock();

        $usefulTagNames = array_diff($this->usefulTags, ['param', 'return']);

        $docReturnTag = $docBlock->getReturnTag();

        if ($docBlock instanceof UndefinedDocBlock
            || $docBlock->hasDescription()
            || $docBlock->hasOneOfTags($usefulTagNames)
            || ($docReturnTag && $docReturnTag->hasDescription())
        ) {
            return false;
        }

        foreach ($fnSig->getParams() as $fnParam) {
            $paramTag = $docBlock->getParamTag($fnParam->getName());
            if (null === $paramTag) {
                continue;
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
}
