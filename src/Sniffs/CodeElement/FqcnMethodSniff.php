<?php

namespace Gskema\TypeSniff\Sniffs\CodeElement;

use Gskema\TypeSniff\Core\Type\TypeComparator;
use Gskema\TypeSniff\Core\Type\TypeHelper;
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
            $paramName = $fnParam->getName();
            $tag = $docBlock->getParamTag($paramName);

            $subject = sprintf('parameter $%s', $paramName);

            $fnType = $fnParam->getType();
            $fnTypeLine = $fnParam->getLine();
            $docType = $tag ? $tag->getType() : null;
            $docTypeLine = $tag ? $tag->getLine() : $fnTypeLine;
            $valueType = $fnParam->getValueType();

            $this->processSigType($file, $docBlock, $subject, $fnType, $fnTypeLine, $docType, $docTypeLine, $valueType);
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
                $docType ? $docType->getLine() : $fnSig->getLine(),
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
        $isReturnType = 'return value' === $subject;
        // $isParamType = !$isReturnType;

        /** @var string[][] $warnings */
        $warnings = [];
        if ($docBlock instanceof UndefinedDocBlock) {
            // Require docType for undefined type or array type
            if ($fnType instanceof UndefinedType) {
                $warnings[$fnTypeLine][] = 'Add type declaration for :subject: or create PHPDoc with type hint';
            } elseif (TypeHelper::containsType($fnType, ArrayType::class)) {
                $warnings[$fnTypeLine][] = 'Create PHPDoc with typed array type hint for :subject:, .e.g.: "string[]" or "SomeClass[]"';
            }
        } elseif (null === $docType) {
            // Require docTag unless void return type
            if ($isReturnType) {
                if (!($fnType instanceof VoidType)) {
                    $warnings[$fnTypeLine][] = 'Missing PHPDoc tag or void type declaration for :subject:';
                }
            } else {
                $warnings[$fnTypeLine][] = 'Missing PHPDoc tag for :subject:';
            }
        } else {
            $docTypeDefined = !($docType instanceof UndefinedType);
            $fnTypeDefined = !($fnType instanceof UndefinedType);

            if ($docTypeDefined) {
                // Require typed array type
                // Require composite with null instead of null
                // @TODO true/void/false/$this/ cannot be param tags

                $docHasTypedArray = TypeHelper::containsType($docType, TypedArrayType::class);
                $docHasArray = TypeHelper::containsType($docType, ArrayType::class);

                if (!$docHasTypedArray && $docHasArray) {
                    $warnings[$docTypeLine][] = 'Replace array type with typed array type in PHPDoc for :subject:. Use mixed[] for generic arrays. Correct array depth must be specified.';
                }

                if ($redundantTypes = TypeComparator::getRedundantDocTypes($docType)) {
                    $warnings[$docTypeLine][] = sprintf('Remove redundant :subject: type hints "%s"', TypeHelper::listRawTypes($redundantTypes));
                }

                if ($docHasTypedArray && $fakeType = TypeHelper::getFakeTypedArrayType($docType)) {
                    $msg = sprintf(
                        'Use a more specific type in typed array hint "%s" for :subject:. Correct array depth must be specified.',
                        $fakeType->toString()
                    );
                    $warnings[$docTypeLine][] = $msg;
                }

                if ($docType instanceof NullType) {
                    if ($isReturnType) {
                        $warnings[$docTypeLine][] = 'Use void :subject :type declaration or change type to compound, e.g. SomeClass|null';
                    } else {
                        $warnings[$docTypeLine][] = 'Change type hint for :subject: to compound, e.g. SomeClass|null';
                    }
                }
            } else {
                // Require docType (example from fnType)
                $exampleDocType = TypeConverter::toExampleDocType($fnType);
                if (null !== $exampleDocType) {
                    $warnings[$docTypeLine][] = sprintf('Add type hint in PHPDoc tag for :subject:, e.g. "%s"', $exampleDocType->toString());
                } else {
                    $warnings[$docTypeLine][] = 'Add type hint in PHPDoc tag for :subject:';
                }
            }

            if (!$fnTypeDefined) {
                // Require fnType if possible (check, suggest from docType)
                if ($suggestedFnType = TypeConverter::toExampleFnType($docType)) {
                    $warnings[$fnTypeLine][] = sprintf('Add type declaration for :subject:, e.g.: "%s"', $suggestedFnType->toString());
                }
            }

            if ($docTypeDefined && $fnTypeDefined) {
                // Require to add missing types to docType,
                if ($fnType instanceof VoidType && $docType instanceof VoidType) {
                    $warnings[$docTypeLine][] = 'Remove @return void tag, not necessary';
                }

                /** @var TypeInterface[] $wrongDocTypes */
                /** @var TypeInterface[] $missingDocTypes */
                [$wrongDocTypes, $missingDocTypes] = TypeComparator::compare($docType, $fnType, $valueType);

                if ($wrongDocTypes) {
                    $warnings[$docTypeLine][] = sprintf(
                        'Type %s "%s" %s not compatible with :subject: type declaration',
                        isset($wrongDocTypes[1]) ? 'hints' : 'hint',
                        TypeHelper::listRawTypes($wrongDocTypes),
                        isset($wrongDocTypes[1]) ? 'are' : 'is'
                    );
                }

                if ($missingDocTypes) {
                    $warnings[$docTypeLine][] = sprintf(
                        'Missing "%s" %s in :subject: type hint',
                        TypeHelper::listRawTypes($missingDocTypes),
                        isset($missingDocTypes[1]) ? 'types' : 'type'
                    );
                }
            }
        }

        foreach ($warnings as $line => $lineWarnings) {
            foreach ($lineWarnings as $warningTpl) {
                $warning = str_replace(':subject:', $subject, $warningTpl);
                $file->addWarningOnLine($warning, $line, 'FqcnMethodSniff');
            }
        }
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
