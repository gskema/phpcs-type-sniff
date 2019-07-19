<?php

namespace Gskema\TypeSniff\Core\Type;

use Gskema\TypeSniff\Core\Type\Common\ArrayType;
use Gskema\TypeSniff\Core\Type\Common\BoolType;
use Gskema\TypeSniff\Core\Type\Common\FloatType;
use Gskema\TypeSniff\Core\Type\Common\FqcnType;
use Gskema\TypeSniff\Core\Type\Common\ObjectType;
use Gskema\TypeSniff\Core\Type\Common\UndefinedType;
use Gskema\TypeSniff\Core\Type\Common\VoidType;
use Gskema\TypeSniff\Core\Type\Declaration\NullableType;
use Gskema\TypeSniff\Core\Type\DocBlock\CompoundType;
use Gskema\TypeSniff\Core\Type\DocBlock\DoubleType;
use Gskema\TypeSniff\Core\Type\DocBlock\FalseType;
use Gskema\TypeSniff\Core\Type\DocBlock\MixedType;
use Gskema\TypeSniff\Core\Type\DocBlock\NullType;
use Gskema\TypeSniff\Core\Type\DocBlock\StaticType;
use Gskema\TypeSniff\Core\Type\DocBlock\ThisType;
use Gskema\TypeSniff\Core\Type\DocBlock\TrueType;
use Gskema\TypeSniff\Core\Type\DocBlock\TypedArrayType;

/**
 * @see TypeConverterTest
 */
class TypeConverter
{
    public static function toExampleDocType(TypeInterface $fnType): ?TypeInterface
    {
        if ($fnType instanceof UndefinedType || $fnType instanceof VoidType) {
            return null;
        }

        if ($fnType instanceof ArrayType) {
            return new TypedArrayType(new FqcnType('SomeClass'), 1);
        }

        if ($fnType instanceof NullableType) {
            $accurateType = static::toExampleDocType($fnType->getType());
            return $accurateType
                ? new CompoundType([$accurateType, new NullType()])
                : null;
        }

        return $fnType;
    }

    public static function toExampleFnType(TypeInterface $docType): ?TypeInterface
    {
        if ($docType instanceof CompoundType) {
            $types = $docType->getTypes();
            if (2 === $docType->getCount() && $docType->containsType(NullType::class)) {
                $otherType = $types[0] instanceof NullType ? $types[1] : $types[0];
                $suggestedType = static::toExampleFnType($otherType);
                if (null !== $suggestedType) {
                    return new NullableType($suggestedType);
                }
            }

            $isNullable = false;
            $isArray = true;
            foreach ($docType->getTypes() as $type) {
                if ($type instanceof NullType) {
                    $isNullable = true;
                    continue;
                }
                if (!($type instanceof ArrayType)
                 && !($type instanceof TypedArrayType)) {
                    $isArray = false;
                    break;
                }
            }

            if ($isArray) {
                return $isNullable
                    ? new NullableType(new ArrayType())
                    : new ArrayType();
            }

            return null;
        }

        if ($docType instanceof NullType) {
            return new NullableType(new FqcnType('SomeClass'));
        }

        // "static", "$this" type hints cannot be forced into "self" type declaration
        // because it is incompatible with extended classes.
        $map = [
            UndefinedType::class => null,
            CompoundType::class => null,
            DoubleType::class => FloatType::class,
            FalseType::class => BoolType::class,
            MixedType::class => null,
            NullType::class => null,
            StaticType::class => null,
            ThisType::class => null,
            TrueType::class => BoolType::class,
            TypedArrayType::class => ArrayType::class,
        ];

        $docTypeClass = get_class($docType);
        if (key_exists($docTypeClass, $map)) {
            $fnTypeClass = $map[$docTypeClass];
            return $fnTypeClass
                ? new $fnTypeClass()
                : null;
        }

        if ($docType instanceof ObjectType
            && version_compare(PHP_VERSION, '7.2', '<')
        ) {
            return null;
        }

        return $docType;
    }
}
