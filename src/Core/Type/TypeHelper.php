<?php

namespace Gskema\TypeSniff\Core\Type;

use Gskema\TypeSniff\Core\Type\Common\ArrayType;
use Gskema\TypeSniff\Core\Type\Common\UndefinedType;
use Gskema\TypeSniff\Core\Type\Declaration\NullableType;
use Gskema\TypeSniff\Core\Type\DocBlock\CompoundType;
use Gskema\TypeSniff\Core\Type\DocBlock\TypedArrayType;

/**
 * @see TypeHelperTest
 */
class TypeHelper
{
    public static function containsType(?TypeInterface $type, string $typeClassName): bool
    {
        return is_a($type, $typeClassName)
            || ($type instanceof CompoundType && $type->containsType($typeClassName))
            || ($type instanceof NullableType && $type->containsType($typeClassName));
    }

    public static function getFakeTypedArrayType(?TypeInterface $type): ?TypedArrayType
    {
        if (null === $type) {
            return null;
        }

        /** @var TypedArrayType[] $typedArrayTypes */
        $typedArrayTypes = [];
        if ($type instanceof CompoundType) {
            $typedArrayTypes = $type->getType(TypedArrayType::class);
        } elseif ($type instanceof TypedArrayType) {
            $typedArrayTypes = [$type];
        }

        foreach ($typedArrayTypes as $typedArrayType) {
            if ($typedArrayType->getType() instanceof ArrayType
             || $typedArrayType->getType() instanceof UndefinedType
            ) {
                return $typedArrayType;  // e.g. array[][] or [][]
            }
        }

        return null;
    }

    /**
     * @param TypeInterface[] $types
     *
     * @return string|null
     */
    public static function listRawTypes(array $types): ?string
    {
        $rawTypes = [];
        foreach ($types as $type) {
            $rawTypes[] = $type->toString();
        }

        return $rawTypes ? implode(', ', $rawTypes) : null;
    }
}
