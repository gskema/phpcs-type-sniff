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
        $rawTypes = array_filter(array_map('trim', explode('|', $rawType)));
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

        if (false !== strpos($rawType, '[]')) {
            $bits = explode('[]', $rawType);
            $bitCount = count($bits);
            if ($bitCount > 1) {
                return new TypedArrayType(static::fromRawType($bits[0]), $bitCount - 1);
            }
        }

        return new FqcnType($rawType);
    }
}
