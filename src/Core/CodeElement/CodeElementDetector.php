<?php

namespace Gskema\TypeSniff\Core\CodeElement;

use Gskema\TypeSniff\Core\CodeElement\Element\EnumConstElement;
use Gskema\TypeSniff\Core\CodeElement\Element\EnumElement;
use Gskema\TypeSniff\Core\CodeElement\Element\EnumMethodElement;
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
            $inFile = [] === array_intersect($path, [T_CLASS, T_TRAIT, T_INTERFACE, T_ANON_CLASS, T_ENUM]) && !$inParentheses;
            $inClass = T_CLASS === end($path) && !$inParentheses;
            $inTrait = T_TRAIT === end($path) && !$inParentheses;
            $inInterface = T_INTERFACE === end($path) && !$inParentheses;
            $inEnum = T_ENUM === end($path) && !$inParentheses;

            // @TODO continue early

            switch ($token['code']) {
                case T_CLASS:
                case T_TRAIT:
                case T_INTERFACE:
                case T_ENUM:
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
                        $extended = TokenHelper::isClassExtended($file, $ptr);
                        $currentElement = new ClassElement($line, $docBlock, $fqcn, [], $extended);
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
                    case T_ENUM:
                        $docBlock = TokenHelper::getPrevDocBlock($file, $ptr, $skip);
                        $attrNames = TokenHelper::getPrevAttributeNames($file, $ptr);
                        $currentElement = new EnumElement($line, $docBlock, $fqcn);
                        $currentElement->setAttributeNames($attrNames);
                        $fileElement->addEnum($currentElement);
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
                        $currentElement = new ClassConstElement($line, $docBlock, $fqcn, $attrNames, $decName, $valueType);
                        $parentElement->addConstant($currentElement);
                        break;
                    case T_VARIABLE:
                        $docBlock = TokenHelper::getPrevPropDocBlock($file, $ptr, $skip);
                        $attrNames = TokenHelper::getPrevPropAttributeNames($file, $ptr);
                        $declType = TokenHelper::getPropDeclarationType($file, $ptr);
                        [$defValueType, $hasDefValue] = TokenHelper::getAssignmentType($file, $ptr);
                        $currentElement = new ClassPropElement($line, $docBlock, $fqcn, [], $decName, $declType, $defValueType, false);
                        $currentElement->setAttributeNames($attrNames);
                        $currentElement->getMetadata()->setHasDefaultValue($hasDefValue);
                        $parentElement->addProperty($currentElement);
                        break;
                    case T_FUNCTION:
                        $extended = static::isExtended($fqcn, $decName, $useReflection);
                        $fnSig = FunctionSignatureParser::fromTokens($file, $ptr);
                        $docBlock = TokenHelper::getPrevDocBlock($file, $ptr, $skip);
                        $attrNames = TokenHelper::getPrevAttributeNames($file, $ptr);
                        $currentElement = new ClassMethodElement($docBlock, $fqcn, [], $fnSig);
                        $currentElement->setAttributeNames($attrNames);
                        $currentElement->getMetadata()->setExtended($extended);
                        $currentElement->getMetadata()->setBasicGetterPropName(TokenHelper::getBasicGetterPropName($file, $ptr));
                        $currentElement->getMetadata()->setNonNullAssignedProps(TokenHelper::getNonNullAssignedProps($file, $ptr));
                        $currentElement->getMetadata()->setThisMethodCalls(TokenHelper::getThisMethodCalls($file, $ptr));
                        $parentElement->addMethod($currentElement);
                        foreach ($currentElement->getPromotedPropParams() as $param) {
                            $parentElement->addProperty(ClassPropElement::fromFunctionParam($param, $fqcn));
                        }
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
                        $currentElement = new TraitPropElement($line, $docBlock, $fqcn, [], $decName, $declType, $defValueType, false);
                        $currentElement->setAttributeNames($attrNames);
                        $currentElement->getMetadata()->setHasDefaultValue($hasDefValue);
                        $parentElement->addProperty($currentElement);
                        break;
                    case T_FUNCTION:
                        $extended = static::isExtended($fqcn, $decName, $useReflection);
                        $fnSig = FunctionSignatureParser::fromTokens($file, $ptr);
                        $docBlock = TokenHelper::getPrevDocBlock($file, $ptr, $skip);
                        $attrNames = TokenHelper::getPrevAttributeNames($file, $ptr);
                        $currentElement = new TraitMethodElement($docBlock, $fqcn, [], $fnSig);
                        $currentElement->setAttributeNames($attrNames);
                        $currentElement->getMetadata()->setExtended($extended);
                        $currentElement->getMetadata()->setBasicGetterPropName(TokenHelper::getBasicGetterPropName($file, $ptr));
                        $currentElement->getMetadata()->setNonNullAssignedProps(TokenHelper::getNonNullAssignedProps($file, $ptr));
                        $currentElement->getMetadata()->setThisMethodCalls(TokenHelper::getThisMethodCalls($file, $ptr));
                        $parentElement->addMethod($currentElement);
                        foreach ($currentElement->getPromotedPropParams() as $param) {
                            $parentElement->addProperty(TraitPropElement::fromFunctionParam($param, $fqcn));
                        }
                        break;
                }
            } elseif ($inInterface) {
                $decName = TokenHelper::getDeclarationName($file, $ptr);
                switch ($tokenCode) {
                    case T_CONST:
                        $docBlock = TokenHelper::getPrevDocBlock($file, $ptr, $skip);
                        $attrNames = TokenHelper::getPrevAttributeNames($file, $ptr);
                        [$valueType,] = TokenHelper::getAssignmentType($file, $ptr);
                        $currentElement = new InterfaceConstElement($line, $docBlock, $fqcn, $attrNames, $decName, $valueType);
                        $parentElement->addConstant($currentElement);
                        break;
                    case T_FUNCTION:
                        $extended = static::isExtended($fqcn, $decName, $useReflection);
                        $fnSig = FunctionSignatureParser::fromTokens($file, $ptr);
                        $docBlock = TokenHelper::getPrevDocBlock($file, $ptr, $skip);
                        $attrNames = TokenHelper::getPrevAttributeNames($file, $ptr);
                        $currentElement = new InterfaceMethodElement($docBlock, $fqcn, [], $fnSig);
                        $currentElement->setAttributeNames($attrNames);
                        $currentElement->getMetadata()->setExtended($extended);
                        $parentElement->addMethod($currentElement);
                        break;
                }
            } elseif ($inEnum) {
                $decName = TokenHelper::getDeclarationName($file, $ptr);
                switch ($tokenCode) {
                    case T_CONST:
                        $docBlock = TokenHelper::getPrevDocBlock($file, $ptr, $skip);
                        $attrNames = TokenHelper::getPrevAttributeNames($file, $ptr);
                        [$valueType,] = TokenHelper::getAssignmentType($file, $ptr);
                        $currentElement = new EnumConstElement($line, $docBlock, $fqcn, $attrNames, $decName, $valueType);
                        $parentElement->addConstant($currentElement);
                        break;
                    case T_FUNCTION:
                        $fnSig = FunctionSignatureParser::fromTokens($file, $ptr);
                        $docBlock = TokenHelper::getPrevDocBlock($file, $ptr, $skip);
                        $attrNames = TokenHelper::getPrevAttributeNames($file, $ptr);
                        $currentElement = new EnumMethodElement($docBlock, $fqcn, [], $fnSig);
                        $currentElement->setAttributeNames($attrNames);
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
        } catch (ReflectionException) {
            return null;
        }
    }
}
