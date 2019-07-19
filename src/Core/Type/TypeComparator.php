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
use Gskema\TypeSniff\Core\Type\Common\UndefinedType;
use Gskema\TypeSniff\Core\Type\Declaration\NullableType;
use Gskema\TypeSniff\Core\Type\DocBlock\CompoundType;
use Gskema\TypeSniff\Core\Type\DocBlock\DoubleType;
use Gskema\TypeSniff\Core\Type\DocBlock\FalseType;
use Gskema\TypeSniff\Core\Type\DocBlock\NullType;
use Gskema\TypeSniff\Core\Type\DocBlock\StaticType;
use Gskema\TypeSniff\Core\Type\DocBlock\ThisType;
use Gskema\TypeSniff\Core\Type\DocBlock\TrueType;
use Gskema\TypeSniff\Core\Type\DocBlock\TypedArrayType;

/**
 * @see \Gskema\TypeSniff\Core\Type\TypeComparatorTest
 */
class TypeComparator
{
    /**
     * If return declaration is "iterable", but PHPDoc has "array",
     * then no warning for wrong/missing type will be issued because "array" is more specific
     * than "iterable".
     *
     * @var string[][]
     */
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
        ThisType::class => [
            CallableType::class,
            FqcnType::class,
            IterableType::class,
            ObjectType::class,
            SelfType::class,
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
     * @param TypeInterface      $docType
     * @param TypeInterface      $fnType
     * @param TypeInterface|null $valueType Const value type, default prop type, default param value type.
     *                                      Null means it wasn't possible to detect the type.
     *
     * @return TypeInterface[][]
     */
    public static function compare(
        TypeInterface $docType,
        TypeInterface $fnType,
        ?TypeInterface $valueType
    ): array {
        $fnTypeDefined = !($fnType instanceof UndefinedType);
        $valTypeDefined = $valueType && !($valueType instanceof UndefinedType);

        $fnTypeMap = [];
        if ($fnTypeDefined) {
            if ($fnType instanceof NullableType) {
                $fnTypeMap[NullType::class] = new NullType();
                $mainFnType = $fnType->getType();
            } else {
                $mainFnType = $fnType;
            }
            $fnTypeMap[get_class($mainFnType)] = $mainFnType;
        }

        if ($valTypeDefined) {
            $fnTypeMap[get_class($valueType)] = $valueType;
        }

        // Both fn and val types are undefined (or not detected), so we cannot check for missing or wrong types
        if (empty($fnTypeMap)) {
            return [[], []];
        }

        $wrongDocTypes = [];
        $missingDocTypeMap = $fnTypeMap;

        $flatDocTypes = $docType instanceof CompoundType ? $docType->getTypes() : [$docType];
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

            // workaround for func1(float $arg1 = 1) :(
            if ($valueType instanceof IntType
                && (FloatType::class === $flatDocTypeClass || DoubleType::class === $flatDocTypeClass)
            ) {
                unset($missingDocTypeMap[IntType::class]);
                $coversFnType = true;
            }

            if (!$coversFnType) {
                $wrongDocTypes[] = $flatDocType;
            }
        }

        $missingDocTypes = array_values($missingDocTypeMap);

        // Assigned value type could not be detected, so we cannot accurately report wrong types.
        // E.g. function func1(int $arg1 = SomeClass::CONST1) - CONST1 may be null and we would
        // report doc type "null" as wrong.
        if (null === $valueType) {
            $wrongDocTypes = [];
        }

        return [$wrongDocTypes, $missingDocTypes];
    }
}
