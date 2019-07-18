<?php

namespace Gskema\TypeSniff\Core\Type;

use Gskema\TypeSniff\Core\Type\Common\ArrayType;
use Gskema\TypeSniff\Core\Type\DocBlock\CompoundType;
use Gskema\TypeSniff\Core\Type\DocBlock\TypedArrayType;

class TypeHelper
{
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
            if ($typedArrayType->getType() instanceof ArrayType) {
                return $typedArrayType;  // e.g. array[][]
            }
        }

        return null;
    }
}
