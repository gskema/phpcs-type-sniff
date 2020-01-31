<?php

namespace Gskema\TypeSniff\Core\CodeElement;

use Gskema\TypeSniff\Core\ReflectionCache;
use Gskema\TypeSniff\Core\TokenHelper;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;
use ReflectionException;
use Gskema\TypeSniff\Core\CodeElement\Element\ClassConstElement;
use Gskema\TypeSniff\Core\CodeElement\Element\ClassElement;
use Gskema\TypeSniff\Core\CodeElement\Element\ClassMethodElement;
use Gskema\TypeSniff\Core\CodeElement\Element\ClassPropElement;
use Gskema\TypeSniff\Core\CodeElement\Element\ConstElement;
use Gskema\TypeSniff\Core\CodeElement\Element\FileElement;
use Gskema\TypeSniff\Core\CodeElement\Element\FunctionElement;
use Gskema\TypeSniff\Core\CodeElement\Element\InterfaceConstElement;
use Gskema\TypeSniff\Core\CodeElement\Element\InterfaceElement;
use Gskema\TypeSniff\Core\CodeElement\Element\InterfaceMethodElement;
use Gskema\TypeSniff\Core\CodeElement\Element\TraitElement;
use Gskema\TypeSniff\Core\CodeElement\Element\TraitMethodElement;
use Gskema\TypeSniff\Core\CodeElement\Element\TraitPropElement;
use Gskema\TypeSniff\Core\Func\FunctionSignatureParser;

/**
 * @see CodeElementDetectorTest
 */
class CodeElementDetector
{
    /**
     * @see https://www.php.net/manual/en/tokens.php
     * @see https://docs.phpdoc.org/glossary.html
     *
     * @param File $file
     * @param bool $useReflection
     *
     * @return FileElement
     */
    public static function detectFromTokens(File $file, bool $useReflection): FileElement
    {
        $namespace = '';
        $className = null;
        $fileElement = null;

        /** @var FileElement|ClassElement|TraitElement|InterfaceElement|null $parentElement */
        $parentElement = null;

        foreach ($file->getTokens() as $ptr => $token) {
            $currentElement = null;
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
                    $className = TokenHelper::getDeclarationName($file, $ptr);
                    break;
                case T_NAMESPACE:
                    $namespace = TokenHelper::getNamespace($file, $ptr);
                    break;
            }

            $fqcn = ($namespace ? $namespace.'\\' : '').$className;

            // Instead of looking for doc blocks , we look for tokens
            // that should be preceded / followed by a doc block. This way we can
            // have "undefined" doc blocks for checking.

            if ($inFile && T_OPEN_TAG === $tokenCode) {
                // @TODO Skip declare?
                $skip = [T_WHITESPACE, T_DECLARE, T_ENDDECLARE];
                $docBlock = TokenHelper::getNextDocBlock($file, $ptr, $skip);
                $fileElement = new FileElement($line, $docBlock, $file->path);
                continue;
            }

            // This covers all elements below. Open tag is processed above.
            $skip = array_merge(Tokens::$methodPrefixes, [T_WHITESPACE]);

            if ($inFile) {
                switch ($tokenCode) {
                    case T_CONST:
                        $constName = TokenHelper::getDeclarationName($file, $ptr);
                        [$valueType,] = TokenHelper::getAssignmentType($file, $ptr);
                        $docBlock = TokenHelper::getPrevDocBlock($file, $ptr, $skip);
                        $currentElement = new ConstElement($line, $docBlock, $namespace, $constName, $valueType);
                        $fileElement->addConstant($currentElement);
                        $parentElement = $fileElement;
                        break;
                    case T_FUNCTION:
                        $docBlock = TokenHelper::getPrevDocBlock($file, $ptr, $skip);
                        $fnSig = FunctionSignatureParser::fromTokens($file, $ptr);
                        $currentElement = new FunctionElement($line, $docBlock, $namespace, $fnSig);
                        $fileElement->addFunction($currentElement);
                        $parentElement = $fileElement;
                        break;
                    case T_CLASS:
                        $docBlock = TokenHelper::getPrevDocBlock($file, $ptr, $skip);
                        $currentElement = new ClassElement($line, $docBlock, $fqcn);
                        $fileElement->addClass($currentElement);
                        $parentElement = $currentElement;
                        break;
                    case T_TRAIT:
                        $docBlock = TokenHelper::getPrevDocBlock($file, $ptr, $skip);
                        $currentElement = new TraitElement($line, $docBlock, $fqcn);
                        $fileElement->addTrait($currentElement);
                        $parentElement = $currentElement;
                        break;
                    case T_INTERFACE:
                        $docBlock = TokenHelper::getPrevDocBlock($file, $ptr, $skip);
                        $currentElement = new InterfaceElement($line, $docBlock, $fqcn);
                        $fileElement->addInterface($currentElement);
                        $parentElement = $currentElement;
                        break;
                }
            } elseif ($inClass) {
                $decName = TokenHelper::getDeclarationName($file, $ptr);
                switch ($tokenCode) {
                    case T_CONST:
                        $docBlock = TokenHelper::getPrevDocBlock($file, $ptr, $skip);
                        [$valueType,] = TokenHelper::getAssignmentType($file, $ptr);
                        $currentElement = new ClassConstElement($line, $docBlock, $fqcn, $decName, $valueType);
                        $parentElement->addConstant($currentElement);
                        break;
                    case T_VARIABLE:
                        $docBlock = TokenHelper::getPrevDocBlock($file, $ptr, $skip);
                        [$defValueType, $hasDefValue] = TokenHelper::getAssignmentType($file, $ptr);
                        $currentElement = new ClassPropElement($line, $docBlock, $fqcn, $decName, $defValueType);
                        $currentElement->getMetadata()->setHasDefaultValue($hasDefValue);
                        $parentElement->addProperty($currentElement);
                        break;
                    case T_FUNCTION:
                        $extended = static::isExtended($fqcn, $decName, $useReflection);
                        $fnSig = FunctionSignatureParser::fromTokens($file, $ptr);
                        $docBlock = TokenHelper::getPrevDocBlock($file, $ptr, $skip);
                        $currentElement = new ClassMethodElement($docBlock, $fqcn, $fnSig);
                        $currentElement->getMetadata()->setExtended($extended);
                        $parentElement->addMethod($currentElement);
                        break;
                }
            } elseif ($inTrait) {
                $decName = TokenHelper::getDeclarationName($file, $ptr);
                switch ($tokenCode) {
                    case T_VARIABLE:
                        $docBlock = TokenHelper::getPrevDocBlock($file, $ptr, $skip);
                        [$defValueType, $hasDefValue] = TokenHelper::getAssignmentType($file, $ptr);
                        $currentElement = new TraitPropElement($line, $docBlock, $fqcn, $decName, $defValueType);
                        $currentElement->getMetadata()->setHasDefaultValue($hasDefValue);
                        $parentElement->addProperty($currentElement);
                        break;
                    case T_FUNCTION:
                        $extended = static::isExtended($fqcn, $decName, $useReflection);
                        $fnSig = FunctionSignatureParser::fromTokens($file, $ptr);
                        $docBlock = TokenHelper::getPrevDocBlock($file, $ptr, $skip);
                        $currentElement = new TraitMethodElement($docBlock, $fqcn, $fnSig);
                        $currentElement->getMetadata()->setExtended($extended);
                        $parentElement->addMethod($currentElement);
                        break;
                }
            } elseif ($inInterface) {
                $decName = TokenHelper::getDeclarationName($file, $ptr);
                switch ($tokenCode) {
                    case T_CONST:
                        $docBlock = TokenHelper::getPrevDocBlock($file, $ptr, $skip);
                        [$valueType,] = TokenHelper::getAssignmentType($file, $ptr);
                        $currentElement = new InterfaceConstElement($line, $docBlock, $fqcn, $decName, $valueType);
                        $parentElement->addConstant($currentElement);
                        break;
                    case T_FUNCTION:
                        $extended = static::isExtended($fqcn, $decName, $useReflection);
                        $fnSig = FunctionSignatureParser::fromTokens($file, $ptr);
                        $docBlock = TokenHelper::getPrevDocBlock($file, $ptr, $skip);
                        $currentElement = new InterfaceMethodElement($docBlock, $fqcn, $fnSig);
                        $currentElement->getMetadata()->setExtended($extended);
                        $parentElement->addMethod($currentElement);
                        break;
                }
            }
        }

        return $fileElement;
    }

    protected static function isExtended(string $fqcn, string $method, bool $useReflection): ?bool
    {
        if (!$useReflection) {
            return null;
        }

        try {
            return in_array($method, ReflectionCache::getMethodsRecursive($fqcn, false));
        } catch (ReflectionException $e) {
            return null;
        }
    }
}
