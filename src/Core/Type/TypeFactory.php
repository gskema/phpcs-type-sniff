<?php

namespace Gskema\TypeSniff\Core\Type;

use Gskema\TypeSniff\Core\Type\Common\ArrayType;
use Gskema\TypeSniff\Core\Type\Common\BoolType;
use Gskema\TypeSniff\Core\Type\Common\CallableType;
use Gskema\TypeSniff\Core\Type\Common\FloatType;
use Gskema\TypeSniff\Core\Type\Common\FqcnType;
use Gskema\TypeSniff\Core\Type\Common\IntType;
use Gskema\TypeSniff\Core\Type\Common\IterableType;
use Gskema\TypeSniff\Core\Type\Common\ObjectType;
use Gskema\TypeSniff\Core\Type\Common\ParentType;
use Gskema\TypeSniff\Core\Type\Common\SelfType;
use Gskema\TypeSniff\Core\Type\Common\StringType;
use Gskema\TypeSniff\Core\Type\Common\UndefinedType;
use Gskema\TypeSniff\Core\Type\Common\VoidType;
use Gskema\TypeSniff\Core\Type\Declaration\NullableType;
use Gskema\TypeSniff\Core\Type\DocBlock\CompoundType;
use Gskema\TypeSniff\Core\Type\DocBlock\DoubleType;
use Gskema\TypeSniff\Core\Type\DocBlock\FalseType;
use Gskema\TypeSniff\Core\Type\DocBlock\MixedType;
use Gskema\TypeSniff\Core\Type\DocBlock\NullType;
use Gskema\TypeSniff\Core\Type\DocBlock\ResourceType;
use Gskema\TypeSniff\Core\Type\DocBlock\StaticType;
use Gskema\TypeSniff\Core\Type\DocBlock\ThisType;
use Gskema\TypeSniff\Core\Type\DocBlock\TrueType;
use Gskema\TypeSniff\Core\Type\DocBlock\TypedArrayType;

/**
 * @see TypeFactoryTest
 */
class TypeFactory
{
    /** @var string[] */
    protected static $basicTypeMap = [
        'array'    => ArrayType::class,
        'bool'     => BoolType::class,
        'boolean'  => BoolType::class,
        'callable' => CallableType::class,
        'double'   => DoubleType::class,
        'false'    => FalseType::class,
        'float'    => FloatType::class,
        'int'      => IntType::class,
        'integer'  => IntType::class,
        'iterable' => IterableType::class,
        'mixed'    => MixedType::class,
        'null'     => NullType::class,
        'object'   => ObjectType::class,
        'parent'   => ParentType::class,
        'resource' => ResourceType::class,
        'self'     => SelfType::class,
        'static'   => StaticType::class,
        'string'   => StringType::class,
        '$this'    => ThisType::class,
        'true'     => TrueType::class,
        ''         => UndefinedType::class,
        'void'     => VoidType::class,
    ];

    public static function fromRawType(string $rawType): TypeInterface
    {
        $rawTypes = array_filter(array_map('trim', static::explode($rawType)));
        if (count($rawTypes) > 1) {
            $types = [];
            foreach ($rawTypes as $rawType) {
                $types[] = static::fromRawType($rawType);
            }

            return new CompoundType($types);
        }
        $rawType = $rawTypes[0] ?? '';

        if ('?' === ($rawType[0] ?? null)) {
            return new NullableType(static::fromRawType(substr($rawType, 1)));
        }

        $basicType = static::$basicTypeMap[$rawType] ?? null;
        if (null !== $basicType) {
            return new $basicType;
        }

        $rawType6 = substr($rawType, 0, 6);
        if (in_array($rawType6, ['array<', 'array{', 'array('])) {
            // e.g.: array<int, string>
            //       array{foo: string, bar: int}
            //       array{b:bool,d:string}[]
            //       array<int, string>[]
            //       array('key' => string, ...)

            // We don't need to parse these type fully and carry their info, because it is not used.
            // Parsing and returning this as mixed[] is enough to prevent array warnings, const/prop/param is documented.
            return new TypedArrayType(new MixedType(), 1);
        }

        // try counting 'int[][]' (array depth)
        $depth = 0;
        $offset = -2;
        while (0 === substr_compare($rawType, '[]', $offset, 2)) {
            $offset -= 2;
            $depth++;
        }

        // e.g.: int[]
        //       \SomeClass[][]
        if ($depth > 0) {
            $rawInnerType = substr($rawType, 0, -2 * $depth);
            return new TypedArrayType(static::fromRawType($rawInnerType), $depth);
        }

        // e.g. (int|string)
        //      ((int|float)[]|(string|bool)[])
        if ('(' === $rawType[0] && ')' === $rawType[-1]) {
            // must not trim more than 1 char on each side!
            return static::fromRawType(substr($rawType, 1, -1));
        }

        return new FqcnType($rawType);
    }

    /**
     * @param string $rawType
     *
     * @return string[]
     */
    protected static function explode(string $rawType): array
    {
        $rawType = trim($rawType); // must be trimmed for logic below to work

        if (empty($rawType)) {
            return [];
        }

        // e.g. 'int|string'
        $hasScopeOpeners = false
            || false !== strpos($rawType, '<')
            || false !== strpos($rawType, '{')
            || false !== strpos($rawType, '(');
        if (!$hasScopeOpeners) {
            return explode('|', $rawType);
        }

        // e.g. array<int|string, int>|null
        //      array{foo: string|int}|array<int|string, array<int>>
        $rawTypes = [];
        $lastSplitPos = 0;
        $openScopes = ['<' => 0, '{' => 0, '(' => 0];

        $len = strlen($rawType);
        for ($pos=0; $pos<=$len-1; $pos++) {
            $ch = $rawType[$pos];

            '<' === $ch && $openScopes['<']++;
            '{' === $ch && $openScopes['{']++;
            '(' === $ch && $openScopes['(']++;

            // We don't want to have negative count in case there are more scope closers than openers.
            // This may occur while editing or just invalid syntax.
            '>' === $ch && $openScopes['<'] > 0 && $openScopes['<']--;
            '}' === $ch && $openScopes['{'] > 0 && $openScopes['{']--;
            ')' === $ch && $openScopes['('] > 0 && $openScopes['(']--;

            if ('|' === $ch && 0 === array_sum($openScopes)) {
                $rawTypes[] = substr($rawType, $lastSplitPos, $pos - $lastSplitPos);
                $lastSplitPos = $pos + 1; // skip current char '|'
            }
        }
        $rawTypes[] = substr($rawType, $lastSplitPos);

        return $rawTypes;
    }
}
