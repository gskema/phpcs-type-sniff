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

            $fqcn = ($namespace ? $namespace . '\\' : '') . $className;

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
                        $attrNames = TokenHelper::getPrevAttributeNames($file, $ptr);
                        $currentElement = new ConstElement($line, $docBlock, $namespace, $constName, $valueType, $attrNames);
                        $fileElement->addConstant($currentElement);
                        $parentElement = $fileElement;
                        break;
                    case T_FUNCTION:
                        $docBlock = TokenHelper::getPrevDocBlock($file, $ptr, $skip);
                        $attrNames = TokenHelper::getPrevAttributeNames($file, $ptr);
                        $fnSig = FunctionSignatureParser::fromTokens($file, $ptr);
                        $currentElement = new FunctionElement($line, $docBlock, $namespace, $fnSig, $attrNames);
                        $fileElement->addFunction($currentElement);
                        $parentElement = $fileElement;
                        break;
                    case T_CLASS:
                        $docBlock = TokenHelper::getPrevDocBlock($file, $ptr, $skip);
                        $attrNames = TokenHelper::getPrevAttributeNames($file, $ptr);
                        $currentElement = new ClassElement($line, $docBlock, $fqcn);
                        $currentElement->setAttributeNames($attrNames);
                        $fileElement->addClass($currentElement);
                        $parentElement = $currentElement;
                        break;
                    case T_TRAIT:
                        $docBlock = TokenHelper::getPrevDocBlock($file, $ptr, $skip);
                        $attrNames = TokenHelper::getPrevAttributeNames($file, $ptr);
                        $currentElement = new TraitElement($line, $docBlock, $fqcn);
                        $currentElement->setAttributeNames($attrNames);
                        $fileElement->addTrait($currentElement);
                        $parentElement = $currentElement;
                        break;
                    case T_INTERFACE:
                        $docBlock = TokenHelper::getPrevDocBlock($file, $ptr, $skip);
                        $attrNames = TokenHelper::getPrevAttributeNames($file, $ptr);
                        $currentElement = new InterfaceElement($line, $docBlock, $fqcn);
                        $currentElement->setAttributeNames($attrNames);
                        $fileElement->addInterface($currentElement);
                        $parentElement = $currentElement;
                        break;
                }
            } elseif ($inClass) {
                $decName = TokenHelper::getDeclarationName($file, $ptr);
                switch ($tokenCode) {
                    case T_CONST:
                        $docBlock = TokenHelper::getPrevDocBlock($file, $ptr, $skip);
                        $attrNames = TokenHelper::getPrevAttributeNames($file, $ptr);
                        [$valueType,] = TokenHelper::getAssignmentType($file, $ptr);
                        $currentElement = new ClassConstElement($line, $docBlock, $fqcn, $decName, $valueType, $attrNames);
                        $parentElement->addConstant($currentElement);
                        break;
                    case T_VARIABLE:
                        $docBlock = TokenHelper::getPrevPropDocBlock($file, $ptr, $skip);
                        $attrNames = TokenHelper::getPrevPropAttributeNames($file, $ptr);
                        $declType = TokenHelper::getPropDeclarationType($file, $ptr);
                        [$defValueType, $hasDefValue] = TokenHelper::getAssignmentType($file, $ptr);
                        $currentElement = new ClassPropElement($line, $docBlock, $fqcn, $decName, $declType, $defValueType);
                        $currentElement->setAttributeNames($attrNames);
                        $currentElement->getMetadata()->setHasDefaultValue($hasDefValue);
                        $parentElement->addProperty($currentElement);
                        break;
                    case T_FUNCTION:
                        $extended = static::isExtended($fqcn, $decName, $useReflection);
                        $fnSig = FunctionSignatureParser::fromTokens($file, $ptr);
                        $docBlock = TokenHelper::getPrevDocBlock($file, $ptr, $skip);
                        $attrNames = TokenHelper::getPrevAttributeNames($file, $ptr);
                        $currentElement = new ClassMethodElement($docBlock, $fqcn, $fnSig);
                        $currentElement->setAttributeNames($attrNames);
                        $currentElement->getMetadata()->setExtended($extended);
                        $currentElement->getMetadata()->setBasicGetterPropName(TokenHelper::getBasicGetterPropName($file, $ptr));
                        $currentElement->getMetadata()->setNonNullAssignedProps(TokenHelper::getNonNullAssignedProps($file, $ptr));
                        $currentElement->getMetadata()->setThisMethodCalls(TokenHelper::getThisMethodCalls($file, $ptr));
                        $parentElement->addMethod($currentElement);
                        break;
                }
            } elseif ($inTrait) {
                $decName = TokenHelper::getDeclarationName($file, $ptr);
                switch ($tokenCode) {
                    case T_VARIABLE:
                        $docBlock = TokenHelper::getPrevPropDocBlock($file, $ptr, $skip);
                        $attrNames = TokenHelper::getPrevPropAttributeNames($file, $ptr);
                        $declType = TokenHelper::getPropDeclarationType($file, $ptr);
                        [$defValueType, $hasDefValue] = TokenHelper::getAssignmentType($file, $ptr);
                        $currentElement = new TraitPropElement($line, $docBlock, $fqcn, $decName, $declType, $defValueType);
                        $currentElement->setAttributeNames($attrNames);
                        $currentElement->getMetadata()->setHasDefaultValue($hasDefValue);
                        $parentElement->addProperty($currentElement);
                        break;
                    case T_FUNCTION:
                        $extended = static::isExtended($fqcn, $decName, $useReflection);
                        $fnSig = FunctionSignatureParser::fromTokens($file, $ptr);
                        $docBlock = TokenHelper::getPrevDocBlock($file, $ptr, $skip);
                        $attrNames = TokenHelper::getPrevAttributeNames($file, $ptr);
                        $currentElement = new TraitMethodElement($docBlock, $fqcn, $fnSig);
                        $currentElement->setAttributeNames($attrNames);
                        $currentElement->getMetadata()->setExtended($extended);
                        $currentElement->getMetadata()->setBasicGetterPropName(TokenHelper::getBasicGetterPropName($file, $ptr));
                        $currentElement->getMetadata()->setNonNullAssignedProps(TokenHelper::getNonNullAssignedProps($file, $ptr));
                        $currentElement->getMetadata()->setThisMethodCalls(TokenHelper::getThisMethodCalls($file, $ptr));
                        $parentElement->addMethod($currentElement);
                        break;
                }
            } elseif ($inInterface) {
                $decName = TokenHelper::getDeclarationName($file, $ptr);
                switch ($tokenCode) {
                    case T_CONST:
                        $docBlock = TokenHelper::getPrevDocBlock($file, $ptr, $skip);
                        $attrNames = TokenHelper::getPrevAttributeNames($file, $ptr);
                        [$valueType,] = TokenHelper::getAssignmentType($file, $ptr);
                        $currentElement = new InterfaceConstElement($line, $docBlock, $fqcn, $decName, $valueType, $attrNames);
                        $parentElement->addConstant($currentElement);
                        break;
                    case T_FUNCTION:
                        $extended = static::isExtended($fqcn, $decName, $useReflection);
                        $fnSig = FunctionSignatureParser::fromTokens($file, $ptr);
                        $docBlock = TokenHelper::getPrevDocBlock($file, $ptr, $skip);
                        $attrNames = TokenHelper::getPrevAttributeNames($file, $ptr);
                        $currentElement = new InterfaceMethodElement($docBlock, $fqcn, $fnSig);
                        $currentElement->setAttributeNames($attrNames);
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
