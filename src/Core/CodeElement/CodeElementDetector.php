<?php

namespace Gskema\TypeSniff\Core\CodeElement;

use Gskema\TypeSniff\Core\Type\Common\ArrayType;
use Gskema\TypeSniff\Core\Type\Common\BoolType;
use Gskema\TypeSniff\Core\Type\Common\FloatType;
use Gskema\TypeSniff\Core\Type\Common\IntType;
use Gskema\TypeSniff\Core\Type\Common\StringType;
use Gskema\TypeSniff\Core\Type\DocBlock\NullType;
use Gskema\TypeSniff\Core\Type\TypeInterface;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;
use ReflectionClass;
use ReflectionException;
use Gskema\TypeSniff\Core\CodeElement\Element\ClassConstElement;
use Gskema\TypeSniff\Core\CodeElement\Element\ClassElement;
use Gskema\TypeSniff\Core\CodeElement\Element\ClassMethodElement;
use Gskema\TypeSniff\Core\CodeElement\Element\ClassPropElement;
use Gskema\TypeSniff\Core\CodeElement\Element\CodeElementInterface;
use Gskema\TypeSniff\Core\CodeElement\Element\ConstElement;
use Gskema\TypeSniff\Core\CodeElement\Element\FileElement;
use Gskema\TypeSniff\Core\CodeElement\Element\FunctionElement;
use Gskema\TypeSniff\Core\CodeElement\Element\InterfaceConstElement;
use Gskema\TypeSniff\Core\CodeElement\Element\InterfaceElement;
use Gskema\TypeSniff\Core\CodeElement\Element\InterfaceMethodElement;
use Gskema\TypeSniff\Core\CodeElement\Element\TraitElement;
use Gskema\TypeSniff\Core\CodeElement\Element\TraitMethodElement;
use Gskema\TypeSniff\Core\CodeElement\Element\TraitPropElement;
use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\DocBlock\DocBlockParser;
use Gskema\TypeSniff\Core\DocBlock\UndefinedDocBlock;
use Gskema\TypeSniff\Core\Func\FunctionSignatureParser;

/**
 * @see CodeElementDetectorTest
 */
class CodeElementDetector
{
    /** @var string[][] [FQCN => string[], ...] */
    protected static $cachedParentMethods = [];

    /**
     * @see https://www.php.net/manual/en/tokens.php
     * @see https://docs.phpdoc.org/glossary.html
     *
     * @param File $file
     * @param bool $useReflection
     *
     * @return CodeElementInterface[]
     */
    public static function detectFromTokens(File $file, bool $useReflection): array
    {
        $tokens = $file->getTokens();

        $namespace = '';
        $className = null;

        $elements = [];
        foreach ($tokens as $ptr => $token) {
            $tokenCode = $token['code'];
            $line = $token['line'];
            $path = $token['conditions'] ?? [];

            // Bracketed namespaces are not supported.
            // This scope detection logic could be a separate class in the future.
            $inParentheses = !empty($token['nested_parenthesis']);
            $inFile = [] === array_intersect($path, [T_CLASS, T_TRAIT, T_INTERFACE, T_ANON_CLASS]) && !$inParentheses;
            $inClass = T_CLASS === end($path) && !$inParentheses;
            $inTrait = T_TRAIT === end($path) && !$inParentheses;
            $inInterface = T_INTERFACE === end($path) && !$inParentheses;

            switch ($token['code']) {
                case T_CLASS:
                case T_TRAIT:
                case T_INTERFACE:
                    $className = static::getDeclarationName($file, $ptr);
                    break;
                case T_NAMESPACE:
                    $namespace = static::getNamespace($file, $ptr);
                    break;
            }

            $fqcn = ($namespace ? $namespace.'\\' : '').$className;

            // Instead of looking for doc blocks , we look for tokens
            // that should be preceded / followed by a doc block. This way we can
            // have "undefined" doc blocks for checking.

            if ($inFile && T_OPEN_TAG === $tokenCode) {
                // @TODO Skip declare?
                $skip = [T_WHITESPACE, T_DECLARE, T_ENDDECLARE];
                $docBlock = static::getNextDocBlock($file, $ptr, $skip);
                $elements[] = new FileElement($line, $docBlock, $file->path);
                continue;
            }

            // This covers all elements below. Open tag is processed above.
            $skip = array_merge(Tokens::$methodPrefixes, [T_WHITESPACE]);

            if ($inFile) {
                switch ($tokenCode) {
                    case T_CONST:
                        $constName = static::getDeclarationName($file, $ptr);
                        $valueType = static::getAssignmentType($file, $ptr);
                        $docBlock = static::getPrevDocBlock($file, $ptr, $skip);
                        $elements[] = new ConstElement($line, $docBlock, $namespace, $constName, $valueType);
                        break;
                    case T_FUNCTION:
                        $docBlock = static::getPrevDocBlock($file, $ptr, $skip);
                        $fnSig = FunctionSignatureParser::fromTokens($file, $ptr);
                        $elements[] = new FunctionElement($line, $docBlock, $namespace, $fnSig);
                        break;
                    case T_CLASS:
                        $docBlock = static::getPrevDocBlock($file, $ptr, $skip);
                        $elements[] = new ClassElement($line, $docBlock, $fqcn);
                        break;
                    case T_TRAIT:
                        $docBlock = static::getPrevDocBlock($file, $ptr, $skip);
                        $elements[] = new TraitElement($line, $docBlock, $fqcn);
                        break;
                    case T_INTERFACE:
                        $docBlock = static::getPrevDocBlock($file, $ptr, $skip);
                        $elements[] = new InterfaceElement($line, $docBlock, $fqcn);
                        break;
                }
            } elseif ($inClass) {
                $decName = static::getDeclarationName($file, $ptr);
                switch ($tokenCode) {
                    case T_CONST:
                        $docBlock = static::getPrevDocBlock($file, $ptr, $skip);
                        $valueType = static::getAssignmentType($file, $ptr);
                        $elements[] = new ClassConstElement($line, $docBlock, $fqcn, $decName, $valueType);
                        break;
                    case T_VARIABLE:
                        $docBlock = static::getPrevDocBlock($file, $ptr, $skip);
                        $defValueType = static::getAssignmentType($file, $ptr);
                        $elements[] = new ClassPropElement($line, $docBlock, $fqcn, $decName, $defValueType);
                        break;
                    case T_FUNCTION:
                        $extended = static::isExtended($fqcn, $decName, $useReflection);
                        $fnSig = FunctionSignatureParser::fromTokens($file, $ptr);
                        $docBlock = static::getPrevDocBlock($file, $ptr, $skip);
                        $elements[] = new ClassMethodElement($docBlock, $fqcn, $fnSig, $extended);
                        break;
                }
            } elseif ($inTrait) {
                $decName = static::getDeclarationName($file, $ptr);
                switch ($tokenCode) {
                    case T_CONST:
                        $docBlock = static::getPrevDocBlock($file, $ptr, $skip);
                        $defValueType = static::getAssignmentType($file, $ptr);
                        $elements[] = new TraitPropElement($line, $docBlock, $fqcn, $decName, $defValueType);
                        break;
                    case T_FUNCTION:
                        $extended = static::isExtended($fqcn, $decName, $useReflection);
                        $fnSig = FunctionSignatureParser::fromTokens($file, $ptr);
                        $docBlock = static::getPrevDocBlock($file, $ptr, $skip);
                        $elements[] = new TraitMethodElement($docBlock, $fqcn, $fnSig, $extended);
                        break;
                }
            } elseif ($inInterface) {
                $decName = static::getDeclarationName($file, $ptr);
                switch ($tokenCode) {
                    case T_CONST:
                        $docBlock = static::getPrevDocBlock($file, $ptr, $skip);
                        $valueType = static::getAssignmentType($file, $ptr);
                        $elements[] = new InterfaceConstElement($line, $docBlock, $fqcn, $decName, $valueType);
                        break;
                    case T_FUNCTION:
                        $extended = static::isExtended($fqcn, $decName, $useReflection);
                        $fnSig = FunctionSignatureParser::fromTokens($file, $ptr);
                        $docBlock = static::getPrevDocBlock($file, $ptr, $skip);
                        $elements[] = new InterfaceMethodElement($docBlock, $fqcn, $fnSig, $extended);
                        break;
                }
            }
        }

        return $elements;
    }

    /**
     * @param File           $file
     * @param int            $startPtr
     * @param int[]|string[] $skip
     *
     * @return DocBlock
     */
    protected static function getPrevDocBlock(File $file, int $startPtr, array $skip): DocBlock
    {
        $docClosePtr = $file->findPrevious($skip, $startPtr - 1, null, true);
        $tokenCode = false === $docClosePtr ? null : $file->getTokens()[$docClosePtr]['code'];

        if (T_DOC_COMMENT_CLOSE_TAG === $tokenCode) {
            $docOpenPtr = $file->findPrevious(T_DOC_COMMENT_OPEN_TAG, $docClosePtr - 1);
            if (false !== $docOpenPtr) {
                return DocBlockParser::fromTokens($file, $docOpenPtr, $docClosePtr);
            }
        }

        return new UndefinedDocBlock();
    }

    /**
     * @param File           $file
     * @param int            $startPtr
     * @param int[]|string[] $skip
     *
     * @return DocBlock
     */
    protected static function getNextDocBlock(File $file, int $startPtr, array $skip): DocBlock
    {
        $docOpenPtr = $file->findNext($skip, $startPtr + 1, null, true);
        $tokenCode = false === $docOpenPtr ? null : $file->getTokens()[$docOpenPtr]['code'];

        if (T_DOC_COMMENT_OPEN_TAG === $tokenCode) {
            $docClosePtr = $file->findNext(T_DOC_COMMENT_CLOSE_TAG, $docOpenPtr + 1);
            if (false !== $docClosePtr) {
                return DocBlockParser::fromTokens($file, $docOpenPtr, $docClosePtr);
            }
        }

        return new UndefinedDocBlock();
    }

    protected static function getNamespace(File $file, int $namespacePtr): string
    {
        $namespace = '';
        $tokens = $file->getTokens();
        $maxPtr = count($tokens) - 1;

        for ($ptr = $namespacePtr + 2; $ptr <= $maxPtr; $ptr++) {
            $tokenCode = $tokens[$ptr]['code'];
            if (T_SEMICOLON === $tokenCode || T_OPEN_CURLY_BRACKET === $tokenCode) {
                break;
            }
            if (T_STRING === $tokenCode || T_NS_SEPARATOR === $tokenCode) {
                $namespace .= $tokens[$ptr]['content'];
            }
        }

        return $namespace;
    }

    protected static function getDeclarationName(File $file, int $ptr): string
    {
        $name = '';
        $namePtr = $file->findNext([T_STRING, T_VARIABLE], $ptr);
        if (false !== $namePtr) {
            $name = $file->getTokens()[$namePtr]['content'];
        }
        if ('$' === ($name[0] ?? null)) {
            $name = substr($name, 1);
        }

        return $name;
    }

    protected static function isExtended(string $fqcn, string $method, bool $useReflection): ?bool
    {
        // @TODO Handle reflection errors
        if (!$useReflection) {
            return null;
        }

        if (!isset(static::$cachedParentMethods[$fqcn])) {
            static::$cachedParentMethods[$fqcn] = static::getMethodsRecursive($fqcn, false);
        }

        return in_array($method, static::$cachedParentMethods[$fqcn]);
    }

    /**
     * @param string $fqcn
     * @param bool   $includeOwn
     *
     * @return string[]
     * @throws ReflectionException
     */
    protected static function getMethodsRecursive(string $fqcn, bool $includeOwn): array
    {
        $classRef = new ReflectionClass($fqcn);

        $methodNames = [];
        if ($includeOwn) {
            foreach ($classRef->getMethods() as $methodRef) {
                $methodNames[] = $methodRef->getName();
            }
        }

        $parentClasses = $classRef->getInterfaceNames();
        if ($classRef->getParentClass()) {
            $parentClasses[] = $classRef->getParentClass()->getName();
        }

        foreach ($parentClasses as $parentClass) {
            $parentMethods = static::getMethodsRecursive($parentClass, true);
            $methodNames = array_merge($methodNames, $parentMethods);
        }

        return $methodNames;
    }

    protected static function getAssignmentType(File $file, int $ptr): ?TypeInterface
    {
        // @TODO Move function somewhere?
        $tokens = $file->getTokens();

        // $ptr is at const or variable, it safer and easier to search backwards
        $semiPtr = $file->findNext([T_SEMICOLON], $ptr + 1);
        if (false === $semiPtr) {
            return null;
        }

        $valueEndPtr = $file->findPrevious(Tokens::$emptyTokens, $semiPtr - 1, null, true);
        if (false === $valueEndPtr) {
            return null;
        }

        $valueToken = $tokens[$valueEndPtr];
        switch ($valueToken['code']) {
            case T_NULL:
                $valueType = new NullType();
                break;
            case T_TRUE:
            case T_FALSE:
                $valueType = new BoolType();
                break;
            case T_LNUMBER:
                $valueType = new IntType();
                break;
            case T_DNUMBER:
                $valueType = new FloatType();
                break;
            case T_CONSTANT_ENCAPSED_STRING:
            case T_END_HEREDOC:
                $valueType = new StringType();
                break;
            case T_CLOSE_SHORT_ARRAY:
            case T_CLOSE_PARENTHESIS: // array()
                $valueType = new ArrayType();
                break;
            default:
                $valueType = null;
        }

        return $valueType;
    }
}
