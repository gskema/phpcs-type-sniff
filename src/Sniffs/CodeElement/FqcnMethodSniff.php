<?php

namespace Gskema\TypeSniff\Sniffs\CodeElement;

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
use Gskema\TypeSniff\Core\Type\DocBlock\CompoundType;
use Gskema\TypeSniff\Core\Type\DocBlock\NullType;
use Gskema\TypeSniff\Core\Type\DocBlock\TypedArrayType;
use Gskema\TypeSniff\Core\Type\TypeConverter;
use Gskema\TypeSniff\Core\Type\TypeInterface;

class FqcnMethodSniff implements CodeElementSniffInterface
{
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
                $file->addWarningOnLine('Missing @inheritDoc tag', $method->getLine(), 'FqcnMethodSniff');
                return;
            }
        }

        // @param
        foreach ($fnSig->getParams() as $fnParam) {
            $paramName = $fnParam->getName();
            $tag = $docBlock->getParamTag($paramName);

            $subject = sprintf('parameter $%s', $paramName);

            $fnType = $fnParam->getType();
            $fnTypeLine = $fnParam->getLine();
            $docType = $tag ? $tag->getType() : null;
            $docTypeLine = $tag ? $tag->getLine() : $fnTypeLine;

            $this->processSigType($file, $docBlock, $subject, $fnType, $fnTypeLine, $docType, $docTypeLine);
        }

        // @return
        if (!$isConstructMethod) {
            $docType = $docBlock->getReturnTag();
            $this->processSigType(
                $file,
                $docBlock,
                'return value',
                $fnSig->getReturnType(),
                $fnSig->getReturnLine(),
                $docType ? $docType->getType() : null,
                $docType ? $docType->getLine() : $fnSig->getLine()
            );
        }
    }

    protected function processSigType(
        File $file,
        DocBlock $docBlock,
        string $subject,
        TypeInterface $fnType,
        int $fnTypeLine,
        ?TypeInterface $docType,
        int $docTypeLine
    ): void {
        // @TODO Required mixed[][] instead of array[]

        $warnings = [];
        if ($docBlock instanceof UndefinedDocBlock) {
            // doc_block:undefined, fn_type:?
            if ($fnType instanceof UndefinedType) {
                $warnings[$fnTypeLine] = 'Add type declaration for :subject: or create PHPDoc with type hint';
            } elseif ($this->containsType($fnType, ArrayType::class)) {
                $warnings[$fnTypeLine] = 'Create PHPDoc with typed array type hint for :subject:, .e.g.: "string[]" or "SomeClass[]"';
            }
        } elseif (null === $docType) {
            // doc_block:defined, doc_tag:missing
            if ('return value' === $subject) { // @TODO ??
                if (!($fnType instanceof VoidType)) {
                    $warnings[$fnTypeLine] = 'Missing PHPDoc tag or void type declaration for :subject:';
                }
            } else {
                $warnings[$fnTypeLine] = 'Missing PHPDoc tag for :subject:';
            }
        } elseif ($docType instanceof UndefinedType) {
            // doc_block:defined, doc_type:undefined
            $suggestedFnType = TypeConverter::toExpectedDocType($fnType);
            if (null !== $suggestedFnType) {
                $warnings[$docTypeLine] = sprintf(
                    'Add type hint in PHPDoc tag for :subject:, e.g. "%s"',
                    $suggestedFnType->toString()
                );
            } else {
                $warnings[$docTypeLine] = 'Add type hint in PHPDoc tag for :subject:';
            }
        } elseif ($fnType instanceof UndefinedType) {
            // doc_block:defined, doc_type:defined, fn_type:undefined
            if ($docType instanceof NullType) {
                $warnings[$fnTypeLine] = sprintf('Add type declaration for :subject:');
            } elseif ($suggestedFnType = TypeConverter::toFunctionType($docType)) {
                $warnings[$fnTypeLine] = sprintf('Add type declaration for :subject:, e.g.: "%s"', $suggestedFnType->toString());
            }
        } elseif ($this->containsType($fnType, ArrayType::class)) {
            // doc_block:defined, doc_type:defined, fn_type:array
            $docHasTypedArray = $this->containsType($docType, TypedArrayType::class);
            $docHasArray = $this->containsType($docType, ArrayType::class);

            if ($docHasTypedArray && $docHasArray) {
                $warnings[$docTypeLine] = 'Remove array type, typed array type is present in PHPDoc for :subject:.';
            } elseif (!$docHasTypedArray && $docHasArray) {
                $warnings[$docTypeLine] = 'Replace array type with typed array type in PHPDoc for :subject:. Use mixed[] for generic arrays.';
            } elseif (!$docHasTypedArray && !$docHasArray) {
                $warnings[$docTypeLine] = 'Add typed array type in PHPDoc for :subject:. Use mixed[] for generic arrays.';
            }
        } elseif ($fnType instanceof NullableType) {
            // doc_block:defined, doc_type:defined, fn_type:nullable
            if (!$this->containsType($docType, NullType::class)) {
                $warnings[$docTypeLine] = 'Add "null" type hint in PHPDoc for :subject:';
            }
        } else {
            // doc_block:defined, doc_type:defined, fn_type:defined
            $expectedDocType = TypeConverter::toExpectedDocType($fnType);
            $expectedDocTypes = $expectedDocType instanceof CompoundType
                ? $expectedDocType->getTypes()
                : array_filter([$expectedDocType]);

            foreach ($expectedDocTypes as $expectedDocType) {
                if (!$this->containsType($docType, get_class($expectedDocType))) {
                    $warnings[$docTypeLine] = sprintf('Add "%s" type hint in PHPDoc for :subject:', $fnType->toString());
                }
            }
        }

        foreach ($warnings as $line => $warningTpl) {
            $warning = str_replace(':subject:', $subject, $warningTpl);
            $file->addWarningOnLine($warning, $line, 'FqcnMethodSniff');
        }
    }

    protected function containsType(TypeInterface $type, string $typeClassName): bool
    {
        return is_a($type, $typeClassName)
            || ($type instanceof CompoundType && $type->containsType($typeClassName))
            || ($type instanceof NullableType && $type->containsType($typeClassName));
    }

    protected function hasUselessDocBlock(AbstractFqcnMethodElement $method): bool
    {
        $fnSig = $method->getSignature();
        $docBlock = $method->getDocBlock();

        $usefulTagNames = ['deprecated', 'throws', 'dataprovider', 'see', 'todo', 'inheritdoc']; // @TODO ??
        $usefulTagNames = array_diff($usefulTagNames, ['param', 'return']);

        $docReturnTag = $docBlock->getReturnTag();

        $hasUsefulDescription = $docBlock->hasDescription()
            && !preg_match('#^\w+\s+constructor\.?$#', $docBlock->getDescription());

        if ($docBlock instanceof UndefinedDocBlock
            || $hasUsefulDescription
            || $docBlock->hasOneOfTags($usefulTagNames)
            || ($docReturnTag && $docReturnTag->hasDescription())
        ) {
            return false;
        }

        $paramTags = $docBlock->getParamTags();
        foreach ($paramTags as $paramTag) {
            if ($paramTag->hasDescription()) {
                return false;
            }
        }

        foreach ($fnSig->getParams() as $fnParam) {
            $paramTag = $docBlock->getParamTag($fnParam->getName());
            if (null === $paramTag) {
                continue;
            }

            if ($paramTag->hasDescription()) {
                return false;
            }

            // @TODO Cleaner way?
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

        // @TODO ??
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
