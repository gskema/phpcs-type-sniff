<?php

namespace Gskema\TypeSniff\Core;

use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\DocBlock\DocBlockParser;
use Gskema\TypeSniff\Core\DocBlock\UndefinedDocBlock;
use Gskema\TypeSniff\Core\Type\Common\ArrayType;
use Gskema\TypeSniff\Core\Type\Common\BoolType;
use Gskema\TypeSniff\Core\Type\Common\FloatType;
use Gskema\TypeSniff\Core\Type\Common\IntType;
use Gskema\TypeSniff\Core\Type\Common\StringType;
use Gskema\TypeSniff\Core\Type\Common\UndefinedType;
use Gskema\TypeSniff\Core\Type\DocBlock\NullType;
use Gskema\TypeSniff\Core\Type\TypeFactory;
use Gskema\TypeSniff\Core\Type\TypeInterface;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;

/**
 * @see TokenHelperTest
 */
class TokenHelper
{
    /**
     * @param File           $file
     * @param int            $propPtr
     * @param int[]|string[] $skip
     *
     * @return DocBlock
     */
    public static function getPrevPropDocBlock(File $file, int $propPtr, array $skip): DocBlock
    {
        $scopePtr = $file->findPrevious(Tokens::$scopeModifiers, $propPtr - 1);
        if (false === $scopePtr) {
            return new UndefinedDocBlock(); // unfinished file
        }

        return self::getPrevDocBlock($file, $scopePtr, $skip);
    }

    /**
     * @param File           $file
     * @param int            $startPtr
     * @param int[]|string[] $skip
     *
     * @return DocBlock
     */
    public static function getPrevDocBlock(File $file, int $startPtr, array $skip): DocBlock
    {
        do {
            $notAttrEndPtr = $file->findPrevious($skip, $startPtr - 1, null, true);
            $tokenCode = false === $notAttrEndPtr ? null : $file->getTokens()[$notAttrEndPtr]['code'];

            $attrOpenPtr = null;
            if (T_ATTRIBUTE_END === $tokenCode) {
                $attrOpenPtr = $file->findPrevious(T_ATTRIBUTE, $notAttrEndPtr - 1);
                $startPtr = $attrOpenPtr ?: null;
            }
        } while (null !== $attrOpenPtr);

        $docClosePtr = $file->findPrevious($skip, $notAttrEndPtr, null, true);
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
    public static function getNextDocBlock(File $file, int $startPtr, array $skip): DocBlock
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

    public static function getNamespace(File $file, int $namespacePtr): string
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

    public static function getDeclarationName(File $file, int $ptr): string
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

    public static function getPropDeclarationType(File $file, int $propNamePtr): TypeInterface
    {
        $endCodes = Tokens::$scopeModifiers;
        $endCodes[] = T_STATIC;

        $tokens = $file->getTokens();

        $typeEndPtr = $file->findPrevious(Tokens::$emptyTokens, $propNamePtr - 1, null, true);
        $code = false !== $typeEndPtr ? $tokens[$typeEndPtr]['code'] : null;

        if (null === $code || in_array($code, $endCodes)) {
            return new UndefinedType();
        }

        $beforeTypeStartPtr = $file->findPrevious(Tokens::$emptyTokens, $typeEndPtr - 1);
        // $beforeTypeStartPtr: false not possible: type token was found = file parsed by phpcs is valid
        $typeStartPtr = $beforeTypeStartPtr + 1;

        $rawType = $file->getTokensAsString($typeStartPtr, $typeEndPtr - $typeStartPtr + 1);

        return TypeFactory::fromRawType($rawType);
    }

    /**
     * @param File $file
     * @param int  $constVarPtr
     *
     * @return mixed[] [?TypeInterface, bool]
     */
    public static function getAssignmentType(File $file, int $constVarPtr): array
    {
        // @TODO Move function somewhere?
        $tokens = $file->getTokens();

        // $ptr is at const or variable (prop), it safer and easier to search backwards
        $semiPtr = $file->findNext([T_SEMICOLON], $constVarPtr + 1);
        if (false === $semiPtr) {
            return [null, false];
        }

        $valueEndPtr = $file->findPrevious(Tokens::$emptyTokens, $semiPtr - 1, null, true);
        // $valueEndPtr will never be false here, since $ptr points to T_CONST, T_VARIABLE

        $valueToken = $tokens[$valueEndPtr];
        switch ($valueToken['code']) {
            case T_CONST:
            case T_VARIABLE:
                return [new UndefinedType(), false];
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
                // We COULD return UndefinedType for T_STRING (no assigment), but this conflicts
                // with values that are other classes' constants (contains T_STRING tokens),
                // where we CANNOT detect the type yet.
                $valueType = null;
        }

        return [$valueType, true];
    }

    public static function getBasicGetterPropName(File $file, int $fnPtr): ?string
    {
        $tokens = $file->getTokens();
        $fnToken = $tokens[$fnPtr];

        // abstract or interface methods do not have scopes
        $openPtr = $fnToken['scope_opener'] ?? null;
        $closePtr = $fnToken['scope_closer'] ?? null;
        if (null === $openPtr || null === $closePtr) {
            return null;
        }

        // return $this->prop;
        $codeSequence = [T_RETURN, T_THIS, T_OBJECT_OPERATOR, T_STRING, T_SEMICOLON];

        $propName = null;
        for ($ptr = $openPtr + 1; $ptr < $closePtr; $ptr++) {
            $token = $tokens[$ptr];
            $code = $token['code'];
            if (in_array($code, Tokens::$emptyTokens)) {
                continue;
            }
            $expectedCode = array_shift($codeSequence);
            if (T_THIS === $expectedCode) {
                if (!static::isThisToken($token)) {
                    return false;
                }
            } elseif ($code !== $expectedCode) {
                return false;
            }
            if (T_STRING === $code) {
                $propName = $token['content'];
            }
        }

        return $propName;
    }

    /**
     * @param File $file
     * @param int  $fnPtr
     *
     * @return string[]
     */
    public static function getNonNullAssignedProps(File $file, int $fnPtr): array
    {
        $tokens = $file->getTokens();
        $fnToken = $tokens[$fnPtr];

        // abstract or interface methods do not have scopes
        $openPtr = $fnToken['scope_opener'] ?? null;
        $closePtr = $fnToken['scope_closer'] ?? null;
        if (null === $openPtr || null === $closePtr) {
            return [];
        }

        $nonNullAssignedProps = [];
        for ($ptr = $openPtr + 1; $ptr < $closePtr; $ptr++) {
            $token = $tokens[$ptr];

            // $this
            if (!static::isThisToken($token)) {
                continue;
            }

            // $this->
            $objOpPtr = $file->findNext(Tokens::$emptyTokens, $ptr + 1, null, true);
            $objOpToken = false === $objOpPtr ? null : $tokens[$objOpPtr];
            if (T_OBJECT_OPERATOR !== $objOpToken['code']) {
                continue;
            }

            // $this->prop
            $propNamePtr = $file->findNext(Tokens::$emptyTokens, $objOpPtr + 1, null, true);
            $propNameToken = false === $propNamePtr ? null : $tokens[$propNamePtr];
            if (T_STRING !== $propNameToken['code']) {
                continue;
            }

            // $this->prop =
            $eqPtr = $file->findNext(Tokens::$emptyTokens, $propNamePtr + 1, null, true);
            $eqToken = false === $eqPtr ? null : $tokens[$eqPtr];
            if (T_EQUAL !== $eqToken['code']) {
                continue;
            }

            // $this->prop = 1
            $nullPtr = $file->findNext(Tokens::$emptyTokens, $eqPtr + 1, null, true);
            $nullToken = false === $nullPtr ? null : $tokens[$nullPtr];
            if (T_NULL !== $nullToken['code']) {
                $nonNullAssignedProps[] = $propNameToken['content'];
                continue;
            }

            // $this->prop = null === $x ? 1: 2
            $semiPtr = $file->findNext(Tokens::$emptyTokens, $nullPtr + 1, null, true);
            $semiToken = false === $semiPtr ? null : $tokens[$semiPtr];
            if (T_SEMICOLON !== $semiToken['code']) {
                $nonNullAssignedProps[] = $propNameToken['content'];
                continue;
            }
        }

        return array_values(array_unique($nonNullAssignedProps));
    }

    /**
     * @param mixed[] $token
     *
     * @return bool
     */
    public static function isThisToken(array $token): bool
    {
        return T_VARIABLE === $token['code'] && '$this' === $token['content'];
    }

    /**
     * @param File $file
     * @param int  $fnPtr
     *
     * @return string[]
     */
    public static function getThisMethodCalls(File $file, int $fnPtr): array
    {
        $tokens = $file->getTokens();
        $fnToken = $tokens[$fnPtr];

        // abstract or interface methods do not have scopes
        $openPtr = $fnToken['scope_opener'] ?? null;
        $closePtr = $fnToken['scope_closer'] ?? null;
        if (null === $openPtr || null === $closePtr) {
            return [];
        }

        // @TODO [$this, 'name']
        $thisMethodCalls = [];
        for ($ptr = $openPtr + 1; $ptr < $closePtr; $ptr++) {
            $token = $tokens[$ptr];

            // $this
            if (!static::isThisToken($token)) {
                continue;
            }

            // $this->
            $objOpPtr = $file->findNext(Tokens::$emptyTokens, $ptr + 1, null, true);
            $objOpToken = false === $objOpPtr ? null : $tokens[$objOpPtr];
            if (T_OBJECT_OPERATOR !== $objOpToken['code']) {
                continue;
            }

            // $this->method
            $methodNamePtr = $file->findNext(Tokens::$emptyTokens, $objOpPtr + 1, null, true);
            $methodNameToken = false === $methodNamePtr ? null : $tokens[$methodNamePtr];
            if (T_STRING !== $methodNameToken['code']) {
                continue;
            }

            // $this->method(
            $eqPtr = $file->findNext(Tokens::$emptyTokens, $methodNamePtr + 1, null, true);
            $eqToken = false === $eqPtr ? null : $tokens[$eqPtr];
            if (T_OPEN_PARENTHESIS !== $eqToken['code']) {
                continue;
            }

            $thisMethodCalls[] = $methodNameToken['content'];
        }

        return array_values(array_unique($thisMethodCalls));
    }

    /**
     * @param File $file
     * @param int  $propPtr
     *
     * @return string[]
     */
    public static function getPrevPropAttributeNames(File $file, int $propPtr): array
    {
        $scopePtr = $file->findPrevious(Tokens::$scopeModifiers, $propPtr - 1);
        if (false === $scopePtr) {
            return []; // unfinished editing
        }

        return static::getPrevAttributeNames($file, $scopePtr);
    }

    /**
     * @param File $file
     * @param int  $ptr
     *
     * @return string[]
     */
    public static function getPrevAttributeNames(File $file, int $ptr): array
    {
        $tokens = $file->getTokens();
        $skip = array_merge(Tokens::$emptyTokens, Tokens::$methodPrefixes, Tokens::$ooScopeTokens);

        $attributeNames = [];

        $searchStartPtr = $ptr;
        while (false !== $attrEndPtr = $file->findPrevious($skip, $searchStartPtr - 1, null, true)) {
            if (T_ATTRIBUTE_END !== $tokens[$attrEndPtr]['code']) {
                break;
            }

            $attrStartPtr = $file->findPrevious(T_ATTRIBUTE, $attrEndPtr - 1);
            if (false === $attrStartPtr) {
                break;
            }

            $rawAttribute = $file->getTokensAsString($attrStartPtr, $attrEndPtr - $attrStartPtr + 1);

            $attributeName = static::parseAttributeName($rawAttribute);
            if (null !== $attributeName) {
                $attributeNames[] = $attributeName;
            }

            $searchStartPtr = $attrStartPtr;
        }

        if (!empty($attributeNames)) {
            $attributeNames = array_values(array_unique(array_reverse($attributeNames)));
        }

        return $attributeNames;
    }

    public static function parseAttributeName(string $rawAttribute): ?string
    {
        preg_match('/#\[([\w\\\\]+)/', $rawAttribute, $matches);

        return $matches[1] ?? null;
    }

    public static function isClassExtended(File $file, int $classPtr): bool
    {
        $classNamePtr = $file->findNext(Tokens::$emptyTokens, $classPtr + 1, null, true);
        if (false === $classNamePtr) {
            return false; // not finished editing?
        }

        $extendsPtr = $file->findNext(Tokens::$emptyTokens, $classNamePtr + 1, null, true);
        $extendsCode = $file->getTokens()[$extendsPtr]['code'] ?? null;

        return T_EXTENDS === $extendsCode;
    }
}
