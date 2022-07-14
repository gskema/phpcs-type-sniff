<?php

namespace Gskema\TypeSniff\Core\Type;

use Gskema\TypeSniff\Core\Type\Common\ArrayType;
use Gskema\TypeSniff\Core\Type\Common\BoolType;
use Gskema\TypeSniff\Core\Type\Common\CallableType;
use Gskema\TypeSniff\Core\Type\Common\FalseType;
use Gskema\TypeSniff\Core\Type\Common\FloatType;
use Gskema\TypeSniff\Core\Type\Common\FqcnType;
use Gskema\TypeSniff\Core\Type\Common\IntType;
use Gskema\TypeSniff\Core\Type\Common\IterableType;
use Gskema\TypeSniff\Core\Type\Common\MixedType;
use Gskema\TypeSniff\Core\Type\Common\NullType;
use Gskema\TypeSniff\Core\Type\Common\ObjectType;
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
use PHPUnit\Framework\TestCase;

class TypeConverterTest extends TestCase
{
    /**
     * @return TypeInterface[][]
     */
    public function dataToExampleDocType(): array
    {
        return [
            [new ArrayType(), new TypedArrayType(new FqcnType('SomeClass'), 1)],
            [new BoolType(), new BoolType()],
            [new CallableType(), new CallableType()],
            [new FloatType(), new FloatType()],
            [new FqcnType('A'), new FqcnType('A')],
            [new IntType(), new IntType()],
            [new IterableType(), new IterableType()],
            [new ObjectType(), new ObjectType()],
            [new ResourceType(), new ResourceType()],
            [new SelfType(), new SelfType()],
            [new StringType(), new StringType()],
            [new UndefinedType(), null],
            [new VoidType(), null],
            [
                new NullableType(new StringType()),
                new UnionType([new StringType(), new NullType()]),
            ],
            [
                new UnionType([new IntType(), new FloatType()]),
                new UnionType([new IntType(), new FloatType()]),
            ],
            [
                new UnionType([new IntType(), new ArrayType()]),
                new UnionType([new IntType(), new TypedArrayType(new FqcnType('SomeClass'), 1)]),
            ],
            [
                new UnionType([new StringType(), new SelfType(), new NullType()]),
                new UnionType([new StringType(), new SelfType(), new NullType()]),
            ],
        ];
    }

    /**
     * @dataProvider dataToExampleDocType
     *
     * @param TypeInterface      $givenFnType
     * @param TypeInterface|null $expectedExampleDocType
     */
    public function testToExampleDocType(
        TypeInterface $givenFnType,
        ?TypeInterface $expectedExampleDocType,
    ): void {
        $actualExampleDocType = TypeConverter::toExampleDocType($givenFnType);

        self::assertEquals($expectedExampleDocType, $actualExampleDocType);
    }

    /**
     * @return TypeInterface[][]
     */
    public function dataToExampleFnType(): array
    {
        return [
            0 => [
                new UnionType([new StringType(), new NullType()]),
                new NullableType(new StringType()),
            ],
            1 => [
                new UnionType([new ThisType(), new NullType()]),
                new NullableType(new StaticType()),
            ],
            2 => [
                new UnionType([new TypedArrayType(new StringType(), 1), new NullType()]),
                new NullableType(new ArrayType()),
            ],
            3 => [
                new UnionType([
                    new TypedArrayType(new StringType(), 1),
                    new FalseType(),
                    new NullType(),
                ]),
                new UnionType([
                    new ArrayType(),
                    new FalseType(),
                    new NullType(),
                ]),
            ],
            4 => [
                new UnionType([
                    new TypedArrayType(new StringType(), 1),
                    new TypedArrayType(new IntType(), 2),
                    new ArrayType(),
                ]),
                new ArrayType(),
            ],
            5 => [
                new UnionType([
                    new TypedArrayType(new StringType(), 1),
                    new TypedArrayType(new IntType(), 2),
                    new ArrayType(),
                    new NullType(),
                ]),
                new NullableType(new ArrayType()),
            ],

            6 => [new UndefinedType(), null],
            7 => [new UnionType([new IntType(), new StringType()]), new UnionType([new IntType(), new StringType()])],
            8 => [new DoubleType(), new FloatType()],
            9 => [new FalseType(), new BoolType()],
            10 => [new MixedType(), new MixedType()],
            11 => [new UnionType([new MixedType(), new NullType()]), new MixedType()],
            12 => [new NullType(), new NullableType(new FqcnType('SomeClass'))],
            13 => [new SelfType(), new SelfType()],
            14 => [new StaticType(), new StaticType()],
            15 => [new ThisType(), new StaticType()],
            16 => [new TrueType(), new BoolType()],
            17 => [new TypedArrayType(new StringType(), 1), new ArrayType()],

            18 => [new ArrayType(), new ArrayType()],
            19 => [new BoolType(), new BoolType()],
            20 => [new CallableType(), new CallableType()],
            21 => [new FloatType(), new FloatType()],
            22 => [new FqcnType('A'), new FqcnType('A')],
            23 => [new IntType(), new IntType()],
            24 => [new IterableType(), new IterableType()],
            25 => [
                new ObjectType(),
                version_compare(PHP_VERSION, '7.2', '<') ? null : new ObjectType(),
            ],
            26 => [new ResourceType(), null],
            27 => [new StringType(), new StringType()],
            28 => [new CallableType(), null, true],
            29 => [
                new UnionType([new NullType(), new FalseType()]),
                null
            ],
            30 => [
                new UnionType([new FqcnType('Acme'), new NullType(), new FalseType()]),
                new UnionType([new FqcnType('Acme'), new NullType(), new FalseType()]),
            ],
            31 => [
                new UnionType([new TypedArrayType(new IntType(), 1), new ArrayType(), new NullType(), new NullType()]),
                new NullableType(new ArrayType()),
            ],
            32 => [
                new UnionType([new IntType(), new CallableType()]),
                null,
                true
            ],
            33 => [
                new UnionType([new FalseType(), new CallableType()]),
                new UnionType([new FalseType(), new CallableType()]),
                false
            ],
        ];
    }

    /**
     * @dataProvider dataToExampleFnType
     *
     * @param TypeInterface $givenDocType
     * @param TypeInterface|null $expectedFnType
     * @param bool $givenIsProp
     */
    public function testToExampleFnType(
        TypeInterface $givenDocType,
        ?TypeInterface $expectedFnType,
        bool $givenIsProp = false,
    ): void {
        $actualFnType = TypeConverter::toExampleFnType($givenDocType, $givenIsProp);

        self::assertEquals($expectedFnType, $actualFnType);
    }
}
