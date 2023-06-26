<?php

namespace Gskema\TypeSniff\Core\Type;

use Gskema\TypeSniff\Core\Type\Common\ArrayType;
use Gskema\TypeSniff\Core\Type\Common\BoolType;
use Gskema\TypeSniff\Core\Type\Common\CallableType;
use Gskema\TypeSniff\Core\Type\Common\FalseType;
use Gskema\TypeSniff\Core\Type\Common\FloatType;
use Gskema\TypeSniff\Core\Type\Common\FqcnType;
use Gskema\TypeSniff\Core\Type\Common\IntersectionType;
use Gskema\TypeSniff\Core\Type\Common\MixedType;
use Gskema\TypeSniff\Core\Type\Common\NeverType;
use Gskema\TypeSniff\Core\Type\Common\NullType;
use Gskema\TypeSniff\Core\Type\Common\StaticType;
use Gskema\TypeSniff\Core\Type\Common\StringType;
use Gskema\TypeSniff\Core\Type\Common\UndefinedType;
use Gskema\TypeSniff\Core\Type\Common\UnionType;
use Gskema\TypeSniff\Core\Type\Common\VoidType;
use Gskema\TypeSniff\Core\Type\Declaration\NullableType;
use Gskema\TypeSniff\Core\Type\DocBlock\ClassStringType;
use Gskema\TypeSniff\Core\Type\DocBlock\DoubleType;
use Gskema\TypeSniff\Core\Type\DocBlock\ResourceType;
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

        if ($fnType instanceof UnionType) {
            return new UnionType(array_map(fn(TypeInterface $type) => static::toExampleDocType($type), $fnType->getTypes()));
        }

        if ($fnType instanceof ArrayType) {
            return new TypedArrayType(new FqcnType('SomeClass'), 1);
        }

        if ($fnType instanceof NullableType) {
            $accurateType = static::toExampleDocType($fnType->getType());
            return $accurateType
                ? new UnionType([$accurateType, new NullType()])
                : null;
        }

        return $fnType;
    }

    public static function toExampleFnType(TypeInterface $docType, bool $isProp): ?TypeInterface
    {
        if ($docType instanceof UnionType) {
            if ($docType->containsType(MixedType::class)) {
                return new MixedType(); // mixed|null -> mixed, mixed type cannot be in union
            }
            if ($docType->containsType(NeverType::class)) {
                return null; // never type can be in fn unions
            }

            // use map to avoid duplicates
            $fnSubTypes = [];
            foreach ($docType->getTypes() as $docSubType) {
                if ($docSubType instanceof NullType || $docSubType instanceof FalseType) {
                    $fnSubTypes[get_class($docSubType)] = $docSubType;
                } elseif ($docSubType instanceof ArrayType || $docSubType instanceof TypedArrayType) {
                    $fnSubTypes[ArrayType::class] = new ArrayType();
                } else {
                    // Sometimes ?int|string can be specified in PHPDoc. don't suggest ?int|string, but null|int|string
                    if ($docSubType instanceof NullableType) {
                        $fnSubTypes[NullType::class] = new NullType();
                        $docSubType = $docSubType->getType();
                    }

                    $fnSubType = static::toExampleFnType($docSubType, $isProp);
                    if (null === $fnSubType) {
                        return null; // cannot be expressed in fn type, e.g. resource or callable (for prop)
                    }
                    $fnSubTypes[$fnSubType->toString()] = $fnSubType; // deduplicate by str expressions, e.g. FqcnType
                }
            }
            $fnSubTypes = array_values($fnSubTypes);

            $fnSubTypeCount = count($fnSubTypes);
            if (2 === $fnSubTypeCount) {
                $isNull0 = $fnSubTypes[0] instanceof NullType;
                $isNull1 = $fnSubTypes[1] instanceof NullType;
                if ($isNull0 || $isNull1) {
                    $otherType = $isNull0 ? $fnSubTypes[1] : $fnSubTypes[0];
                    return $otherType instanceof FalseType ? null : new NullableType($otherType); // null|false not possible
                }
            } elseif (1 === $fnSubTypeCount) {
                return $fnSubTypes[0];
            }

            return new UnionType($fnSubTypes);
        }

        if ($docType instanceof IntersectionType && !$docType->isValid()) {
            return null;
        }

        // e.g. suggestion when func1($arg1 = null) -> generated phpdoc is usually @param null $arg1
        if ($docType instanceof NullType) {
            return new NullableType(new FqcnType('SomeClass'));
        }

        $map = [
            UndefinedType::class => null,
            DoubleType::class => FloatType::class,
            FalseType::class => BoolType::class, // false stand-alone only available in php8.1
            NullType::class => null, // null stand-alone only available in php8.1
            ThisType::class => StaticType::class,
            TrueType::class => BoolType::class,
            TypedArrayType::class => ArrayType::class,
            ResourceType::class => null,
            ClassStringType::class => StringType::class,
        ];

        $docTypeClass = get_class($docType);
        if (key_exists($docTypeClass, $map)) {
            $fnTypeClass = $map[$docTypeClass];
            return $fnTypeClass ? new $fnTypeClass() : null;
        }

        if ($isProp && $docType instanceof CallableType) {
            return null; // supported for function arguments, but not props
        }

        return $docType;
    }
}
