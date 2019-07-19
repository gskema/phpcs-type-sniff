<?php

namespace Gskema\TypeSniff\Core\Func;

use Gskema\TypeSniff\Core\Type\Common\FqcnType;
use PHP_CodeSniffer\Files\File;
use RuntimeException;
use Gskema\TypeSniff\Core\Type\TypeFactory;

/**
 * @see FunctionSignatureParserTest
 */
class FunctionSignatureParser
{
    public static function fromTokens(File $file, int $fnPtr): FunctionSignature
    {
        /** @see File::getMethodParameters() */
        /** @see File::getMethodProperties() */

        $tokens = $file->getTokens();

        $fnName = null;
        $fnNameLine = null;
        $returnLine = null;

        $ptr = $fnPtr + 1; // skip T_WHITESPACE
        while (isset($tokens[++$ptr])) {
            $token = $tokens[$ptr];
            switch ($token['code']) {
                case T_STRING:
                    $fnName = $token['content'];
                    $fnNameLine = $token['line'];
                    break;
                case T_OPEN_PARENTHESIS:
                    break 2;
            }
        }
        if (null === $fnName) {
            throw new RuntimeException('Expected to find function name');
        }

        /** @see https://www.php.net/manual/en/tokens.php */
        /** @var FunctionParam[] $params */
        $params = [];
        $raw = [];
        while (isset($tokens[++$ptr])) {
            $token = $tokens[$ptr];

            switch ($token['code']) {
                case T_CONSTANT_ENCAPSED_STRING:
                    $raw['default'] = 'string';
                    break;
                case T_LNUMBER:
                    $raw['default'] = 'int';
                    break;
                case T_DNUMBER:
                    $raw['default'] = 'float';
                    break;
                case T_NULL:
                    $raw['default'] = 'null';
                    break;
                case T_FALSE:
                case T_TRUE:
                    $raw['default'] = 'bool';
                    break;
                case T_ARRAY:
                    $raw['default'] = 'array';
                    $ptr = $file->findNext(T_CLOSE_PARENTHESIS, $ptr + 1) ?: $ptr;
                    break;
                case T_OPEN_SHORT_ARRAY:
                    $raw['default'] = 'array';
                    $ptr = $file->findNext(T_CLOSE_SHORT_ARRAY, $ptr + 1) ?: $ptr;
                    break;
                case T_PARENT:
                case T_CALLABLE:
                case T_NULLABLE:
                    // these cannot be default
                    $raw['type'] = ($raw['type'] ?? '').$token['content'];
                    break;
                case T_EQUAL:
                    $raw['default'] = '';
                    break;
                case T_STRING:
                case T_SELF:
                case T_DOUBLE_COLON:
                case T_NS_SEPARATOR:
                    if (isset($raw['default'])) {
                        $raw['default'] .= $token['content'];
                    } else {
                        $raw['type'] = ($raw['type'] ?? '').$token['content'];
                    }
                    break;
                case T_ELLIPSIS:
                    $raw['variable_length'] = true;
                    break;
                case T_BITWISE_AND:
                    $raw['pass_be_reference'] = true;
                    break;
                case T_VARIABLE:
                    $raw['name'] = substr($token['content'], 1);
                    $raw['line'] = $token['line'];
                    break;

                case T_COMMA:
                    if (!empty($raw)) {
                        $params[] = static::createParam($raw);
                        $raw = [];
                    }
                    break;
                case T_CLOSE_PARENTHESIS:
                    $returnLine = $token['line'];
                    if (!empty($raw)) {
                        $params[] = static::createParam($raw);
                    }
                    break 2;
            }
        }

        $rawReturnType = '';
        while (isset($tokens[++$ptr])) {
            $token = $tokens[$ptr];
            switch ($token['code']) {
                case T_SELF:
                case T_CALLABLE:
                case T_NULLABLE:
                case T_STRING:
                case T_NS_SEPARATOR:
                    $returnLine = $token['line'];
                    $rawReturnType .= $token['content'];
                    break;
                case T_SEMICOLON:
                case T_OPEN_CURLY_BRACKET:
                    break 2;
            }
        }
        $returnType = TypeFactory::fromRawType($rawReturnType);

        return new FunctionSignature(
            $fnNameLine,
            $fnName,
            $params,
            $returnType,
            $returnLine
        );
    }

    /**
     * @param mixed[] $raw
     *
     * @return FunctionParam
     */
    protected static function createParam(array $raw): FunctionParam
    {
        $rawValueType = $raw['default'] ?? '';
        if (false !== strpos($rawValueType, '::')) {
            $valueType = null; // a constant is used, need reflection :(
        } else {
            $valueType = TypeFactory::fromRawType($raw['default'] ?? '');
        }

        if ($valueType instanceof FqcnType) {
            $valueType = null; // e.g $arg = PHP_INT_MAX // @TODO
        }

        return new FunctionParam(
            $raw['line'],
            $raw['name'],
            TypeFactory::fromRawType($raw['type'] ?? ''),
            $valueType
        );
    }
}
