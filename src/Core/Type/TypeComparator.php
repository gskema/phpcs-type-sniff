<?php

namespace Gskema\TypeSniff\Core\Type;

use Gskema\TypeSniff\Core\Type\Common\ArrayType;
use Gskema\TypeSniff\Core\Type\Common\BoolType;
use Gskema\TypeSniff\Core\Type\Common\CallableType;
use Gskema\TypeSniff\Core\Type\Common\UnionType;
use Gskema\TypeSniff\Core\Type\Common\FloatType;
use Gskema\TypeSniff\Core\Type\Common\FqcnType;
use Gskema\TypeSniff\Core\Type\Common\IntType;
use Gskema\TypeSniff\Core\Type\Common\IterableType;
use Gskema\TypeSniff\Core\Type\Common\ObjectType;
use Gskema\TypeSniff\Core\Type\Common\ParentType;
use Gskema\TypeSniff\Core\Type\Common\SelfType;
use Gskema\TypeSniff\Core\Type\Common\StaticType;
use Gskema\TypeSniff\Core\Type\Common\UndefinedType;
use Gskema\TypeSniff\Core\Type\Declaration\NullableType;
use Gskema\TypeSniff\Core\Type\DocBlock\DoubleType;
use Gskema\TypeSniff\Core\Type\DocBlock\FalseType;
use Gskema\TypeSniff\Core\Type\DocBlock\NullType;
use Gskema\TypeSniff\Core\Type\DocBlock\ThisType;
use Gskema\TypeSniff\Core\Type\DocBlock\TrueType;
use Gskema\TypeSniff\Core\Type\DocBlock\TypedArrayType;

/**
 * @see \Gskema\TypeSniff\Core\Type\TypeComparatorTest
 */
class TypeComparator
{
    /** @var string[] */
    protected static array $redundantTypeMap = [
        ArrayType::class => TypedArrayType::class,
        DoubleType::class => FloatType::class,
        FalseType::class => BoolType::class,
        TrueType::class => BoolType::class,
    ];

    /**
     * If return declaration is "iterable", but PHPDoc has "array",
     * then no warning for wrong/missing type will be issued because "array" is more specific
     * than "iterable".
     *
     * More specific types are useful when chain calling, e.g.:
     * $acme->makeCallable()->specificAcmeMethod() when makeCallable(): callable has PHPDoc
     * with specific return type: self, static, $this, FQCN.
     *
     * @var string[][]
     */
    protected static array $coveredFnTypeClassMap = [
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
            // SelfType::class, // '$this' doesn't provide any additional code intel over 'self', better trim PHPDoc.
        ],
        TrueType::class => [
            BoolType::class,
        ],
        TypedArrayType::class => [
            ArrayType::class,
            IterableType::class,
            FqcnType::class, // e.g. Collection|Image[]
        ],
    ];

    /**
     * @param TypeInterface      $docType
     * @param TypeInterface      $fnType
     * @param TypeInterface|null $valueType Const value type, default prop type, default param value type.
     *                                      Null means it wasn't possible to detect the type.
     * @param bool               $isProp
     *
     * @return TypeInterface[][]
     */
    public static function compare(
        TypeInterface $docType,
        TypeInterface $fnType,
        ?TypeInterface $valueType,
        bool $isProp,
    ): array {
        $docTypeDefined = !($docType instanceof UndefinedType);
        $fnTypeDefined = !($fnType instanceof UndefinedType);
        $valTypeDefined = $valueType && !($valueType instanceof UndefinedType);

        if (!$docTypeDefined) {
            return [[], []];
        }

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

        $flatDocTypes = $docType instanceof UnionType ? $docType->getTypes() : [$docType];
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
            if (
                $valueType instanceof IntType
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
        // report doc type "null" as wrong. This is not relevant for props.
        if (null === $valueType && !$isProp) {
            $wrongDocTypes = [];
        }

        return [$wrongDocTypes, $missingDocTypes];
    }

    /**
     * @param TypeInterface|null $type
     *
     * @return TypeInterface[]
     */
    public static function getRedundantDocTypes(?TypeInterface $type): array
    {
        // mixed type redundancies are not reported - it's fine to document mixed|null or mixed|Acme
        // to make a particular type stand out among mixed

        $redundantTypes = [];
        if ($type instanceof UnionType) {
            foreach ($type->getTypes() as $innerType) {
                $expectedTypeClass = static::$redundantTypeMap[get_class($innerType)] ?? null;
                if ($expectedTypeClass && $type->containsType($expectedTypeClass)) {
                    $redundantTypes[] = $innerType;
                }
            }
        }

        return $redundantTypes;
    }
}
