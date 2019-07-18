<?php

namespace Gskema\TypeSniff\Core\Type;

use Gskema\TypeSniff\Core\Type\Common\ArrayType;
use Gskema\TypeSniff\Core\Type\Common\BoolType;
use Gskema\TypeSniff\Core\Type\Common\CallableType;
use Gskema\TypeSniff\Core\Type\Common\FloatType;
use Gskema\TypeSniff\Core\Type\Common\FqcnType;
use Gskema\TypeSniff\Core\Type\Common\IterableType;
use Gskema\TypeSniff\Core\Type\Common\ObjectType;
use Gskema\TypeSniff\Core\Type\Common\ParentType;
use Gskema\TypeSniff\Core\Type\Common\SelfType;
use Gskema\TypeSniff\Core\Type\Common\UndefinedType;
use Gskema\TypeSniff\Core\Type\Declaration\NullableType;
use Gskema\TypeSniff\Core\Type\DocBlock\CompoundType;
use Gskema\TypeSniff\Core\Type\DocBlock\DoubleType;
use Gskema\TypeSniff\Core\Type\DocBlock\FalseType;
use Gskema\TypeSniff\Core\Type\DocBlock\NullType;
use Gskema\TypeSniff\Core\Type\DocBlock\StaticType;
use Gskema\TypeSniff\Core\Type\DocBlock\TrueType;
use Gskema\TypeSniff\Core\Type\DocBlock\TypedArrayType;

/**
 * @see \Gskema\TypeSniff\Core\Type\TypeComparatorTest
 */
class TypeComparator
{
    /** @var string[][] */
    protected static $coveredFnTypeClassMap = [
        ArrayType::class => [
            IterableType::class,
        ],
        FqcnType::class => [
            CallableType::class,
            IterableType::class,
            ObjectType::class,
            ParentType::class,
            SelfType::class,
        ],
        ParentType::class => [
            CallableType::class,
            FqcnType::class,
            IterableType::class,
            ObjectType::class,
        ],
        SelfType::class => [
            CallableType::class,
            FqcnType::class,
            IterableType::class,
            ObjectType::class,
        ],
        DoubleType::class => [
            FloatType::class,
        ],
        FalseType::class => [
            BoolType::class,
        ],
        StaticType::class => [
            CallableType::class,
            FqcnType::class,
            IterableType::class,
            ObjectType::class,
            ParentType::class,
        ],
        TrueType::class => [
            BoolType::class,
        ],
        TypedArrayType::class => [
            ArrayType::class,
            IterableType::class,
        ],
    ];

    /**
     * @param TypeInterface $docType
     * @param TypeInterface $fnType
     *
     * @return TypeInterface[][]
     */
    public static function compare(TypeInterface $docType, TypeInterface $fnType): array
    {
        if ($fnType instanceof UndefinedType) {
            return [[], []];
        }

        $fnTypeMap = [];
        if ($fnType instanceof NullableType) {
            $fnTypeMap[NullType::class] = new NullType();
            $mainFnType = $fnType->getType();
        } else {
            $mainFnType = $fnType;
        }
        $fnTypeMap[get_class($mainFnType)] = $mainFnType;

        $flatDocTypes = $docType instanceof CompoundType ? $docType->getTypes() : [$docType];

        $wrongDocTypes = [];
        $missingDocTypeMap = $fnTypeMap;
        foreach ($flatDocTypes as $flatDocType) {
            $flatDocTypeClass = get_class($flatDocType);
            $coveredFnTypeClasses = static::$coveredFnTypeClassMap[$flatDocTypeClass] ?? [];
            $coveredFnTypeClasses[] = $flatDocTypeClass;

            $coversFnType = false;
            foreach ($coveredFnTypeClasses as $coveredFnTypeClass) {
                if (key_exists($coveredFnTypeClass, $fnTypeMap)) {
                    $coversFnType = true;
                    unset($missingDocTypeMap[$coveredFnTypeClass]);
                    break;
                }
            }

            if (!$coversFnType) {
                $wrongDocTypes[] = $flatDocType;
            }
        }

        $missingDocTypes = array_values($missingDocTypeMap);

        return [$wrongDocTypes, $missingDocTypes];
    }
}
