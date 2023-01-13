<?php

namespace Gskema\TypeSniff\Core\Type;

use Gskema\TypeSniff\Core\Type\Common\ArrayType;
use Gskema\TypeSniff\Core\Type\Common\BoolType;
use Gskema\TypeSniff\Core\Type\Common\CallableType;
use Gskema\TypeSniff\Core\Type\Common\FalseType;
use Gskema\TypeSniff\Core\Type\Common\FloatType;
use Gskema\TypeSniff\Core\Type\Common\FqcnType;
use Gskema\TypeSniff\Core\Type\Common\IntersectionType;
use Gskema\TypeSniff\Core\Type\Common\IntType;
use Gskema\TypeSniff\Core\Type\Common\IterableType;
use Gskema\TypeSniff\Core\Type\Common\MixedType;
use Gskema\TypeSniff\Core\Type\Common\NeverType;
use Gskema\TypeSniff\Core\Type\Common\NullType;
use Gskema\TypeSniff\Core\Type\Common\ObjectType;
use Gskema\TypeSniff\Core\Type\Common\ParentType;
use Gskema\TypeSniff\Core\Type\Common\SelfType;
use Gskema\TypeSniff\Core\Type\Common\StaticType;
use Gskema\TypeSniff\Core\Type\Common\StringType;
use Gskema\TypeSniff\Core\Type\Common\UndefinedType;
use Gskema\TypeSniff\Core\Type\Common\UnionType;
use Gskema\TypeSniff\Core\Type\Common\VoidType;
use Gskema\TypeSniff\Core\Type\Declaration\NullableType;
use Gskema\TypeSniff\Core\Type\DocBlock\DoubleType;
use Gskema\TypeSniff\Core\Type\DocBlock\ResourceType;
use Gskema\TypeSniff\Core\Type\DocBlock\ThisType;
use Gskema\TypeSniff\Core\Type\DocBlock\TrueType;
use Gskema\TypeSniff\Core\Type\DocBlock\TypedArrayType;

/**
 * @see TypeFactoryTest
 */
class TypeFactory
{
    /** @var string[] */
    protected static array $basicTypeMap = [
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
        'never'    => NeverType::class,
    ];

    /**
     * @param string $rawType
     *
     * @return mixed[]
     */
    public static function fromRawTypeAndDescription(string $rawType): array
    {
        [$rawTypes, $description] = static::split($rawType);

        $type = static::fromRawTypes($rawTypes);

        return [$type, $description];
    }

    public static function fromRawType(string $rawType): TypeInterface
    {
        [$rawTypes, ] = static::split($rawType);

        return static::fromRawTypes($rawTypes);
    }

    public static function fromValue(mixed $value): TypeInterface
    {
        $type = gettype($value);
        $map = ['double' => 'float', 'NULL' => 'null'];
        return self::fromRawType($map[$type] ?? $type);
    }

    /**
     * @TODO This does not support spacing in unions and intersections, e.g. (int | string)
     * @param string[] $rawTypes
     * @return TypeInterface
     */
    protected static function fromRawTypes(array $rawTypes): TypeInterface
    {
        $rawTypes = array_filter(array_map('trim', $rawTypes));
        if (count($rawTypes) > 1) {
            $types = [];
            foreach ($rawTypes as $rawType) {
                $types[] = static::fromRawTypes([$rawType]); // skip split
            }

            return new UnionType($types);
        }
        $rawType = $rawTypes[0] ?? '';

        if (str_contains($rawType, '&')) {
            return new IntersectionType(array_map(self::fromRawType(...), explode('&', $rawType)));
        }

        // Supported for parsing function parameter type declaration, but doc type not valid.
        // Usage as doc type detected by multiple warnings.
        if ('?' === ($rawType[0] ?? null)) {
            return new NullableType(static::fromRawTypes([substr($rawType, 1)])); // skip split
        }

        $basicType = static::$basicTypeMap[$rawType] ?? null;
        if (null !== $basicType) {
            return new $basicType();
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
        // edge case: [][], check if offset still valid
        $len = strlen($rawType);
        $depth = 0;
        $offset = -2;
        while ($len + $offset >= 0 && 0 === substr_compare($rawType, '[]', $offset, 2)) {
            $offset -= 2;
            $depth++;
        }

        // e.g.: int[]
        //       \SomeClass[][]
        if ($depth > 0) {
            $rawInnerType = substr($rawType, 0, -2 * $depth);

            return new TypedArrayType(static::fromRawTypes([$rawInnerType]), $depth);
        }

        // e.g. (int|string)
        //      ((int|float)[]|(string|bool)[])
        if ('(' === $rawType[0] && ')' === $rawType[-1]) {
            // must not trim more than 1 char on each side!
            return static::fromRawType(substr($rawType, 1, -1)); // cannot skip split
        }

        return new FqcnType($rawType);
    }

    /**
     * @param string $tagBody
     *
     * @return mixed[]
     */
    protected static function split(string $tagBody): array
    {
        // e.g. @param array<int|string, int>|null $param1
        //      @return array{foo: string|int}|array<int|string, array<int>> Description for return tag
        //      @param int|string

        $tagBody = trim($tagBody);

        // Shortcut to skip loop below. No need to check strings like 'array<' because we split raw type + description
        // by ' ' char.
        if (
            !str_contains($tagBody, '<') &&
            !str_contains($tagBody, '{') &&
            !str_contains($tagBody, '(')
        ) {
            $spacePos = strpos($tagBody, ' ');
            $rawType = false !== $spacePos ? substr($tagBody, 0, $spacePos) : $tagBody;
            $remainingString = false !== $spacePos ? substr($tagBody, $spacePos + 1) : '';

            $rawTypes = array_filter(array_map('trim', explode('|', $rawType)));
            $remainingString = trim($remainingString);

            return [$rawTypes, $remainingString];
        }

        $rawTypes = [];
        $remainingString = null;
        $lastSplitPos = 0;
        $openScopes = ['<' => 0, '{' => 0, '(' => 0];
        $len = strlen($tagBody);
        for ($pos = 0; $pos <= $len - 1; $pos++) {
            $ch = $tagBody[$pos];

            '<' === $ch && $openScopes['<']++;
            '{' === $ch && $openScopes['{']++;
            '(' === $ch && $openScopes['(']++;

            // We don't want to have negative count in case there are more scope closers than openers.
            // This may occur while editing or just invalid syntax.
            '>' === $ch && $openScopes['<'] > 0 && $openScopes['<']--;
            '}' === $ch && $openScopes['{'] > 0 && $openScopes['{']--;
            ')' === $ch && $openScopes['('] > 0 && $openScopes['(']--;

            $openScopeCount = array_sum($openScopes);
            if ('|' === $ch && 0 === $openScopeCount) {
                $rawTypes[] = substr($tagBody, $lastSplitPos, $pos - $lastSplitPos);
                $lastSplitPos = $pos + 1; // skip current char '|'
            }
            if (' ' === $ch && 0 === $openScopeCount) {
                $rawTypes[] = substr($tagBody, $lastSplitPos, $pos - $lastSplitPos);
                $remainingString = substr($tagBody, $pos);
                break;
            }
        }
        if (null === $remainingString) {
            $rawTypes[] = substr($tagBody, $lastSplitPos);
        }

        $rawTypes = array_filter(array_map('trim', $rawTypes));
        $remainingString = null !== $remainingString ? trim($remainingString) : null;

        return [$rawTypes, $remainingString];
    }
}
