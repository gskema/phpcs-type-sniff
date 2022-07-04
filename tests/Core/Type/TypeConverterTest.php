<?php

namespace Gskema\TypeSniff\Core\Type;

use Gskema\TypeSniff\Core\Type\DocBlock\ResourceType;
use PHPUnit\Framework\TestCase;
use Gskema\TypeSniff\Core\Type\Common\ArrayType;
use Gskema\TypeSniff\Core\Type\Common\BoolType;
use Gskema\TypeSniff\Core\Type\Common\CallableType;
use Gskema\TypeSniff\Core\Type\Common\FloatType;
use Gskema\TypeSniff\Core\Type\Common\FqcnType;
use Gskema\TypeSniff\Core\Type\Common\IntType;
use Gskema\TypeSniff\Core\Type\Common\IterableType;
use Gskema\TypeSniff\Core\Type\Common\ObjectType;
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
use Gskema\TypeSniff\Core\Type\DocBlock\StaticType;
use Gskema\TypeSniff\Core\Type\DocBlock\ThisType;
use Gskema\TypeSniff\Core\Type\DocBlock\TrueType;
use Gskema\TypeSniff\Core\Type\DocBlock\TypedArrayType;

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
                new CompoundType([new StringType(), new NullType()])
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
        ?TypeInterface $expectedExampleDocType
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
            [
                new CompoundType([new StringType(), new NullType()]),
                new NullableType(new StringType()),
            ],
            [
                new CompoundType([new ThisType(), new NullType()]),
                null,
            ],
            [
                new CompoundType([new TypedArrayType(new StringType(), 1), new NullType()]),
                new NullableType(new ArrayType()),
            ],
            [
                new CompoundType([
                    new TypedArrayType(new StringType(), 1),
                    new FalseType(),
                    new NullType()
                ]),
                null,
            ],
            [
                new CompoundType([
                    new TypedArrayType(new StringType(), 1),
                    new TypedArrayType(new IntType(), 2),
                    new ArrayType(),
                ]),
                new ArrayType(),
            ],
            [
                new CompoundType([
                    new TypedArrayType(new StringType(), 1),
                    new TypedArrayType(new IntType(), 2),
                    new ArrayType(),
                    new NullType()
                ]),
                new NullableType(new ArrayType()),
            ],

            [new UndefinedType(), null],
            [new CompoundType([new IntType(), new StringType()]), null],
            [new DoubleType(), new FloatType()],
            [new FalseType(), new BoolType()],
            [new MixedType(), null],
            [new NullType(), new NullableType(new FqcnType('SomeClass'))],
            [new SelfType(), new SelfType()],
            [new StaticType(), null],
            [new ThisType(), null],
            [new TrueType(), new BoolType()],
            [new TypedArrayType(new StringType(), 1), new ArrayType()],

            [new ArrayType(), new ArrayType()],
            [new BoolType(), new BoolType()],
            [new CallableType(), new CallableType()],
            [new FloatType(), new FloatType()],
            [new FqcnType('A'), new FqcnType('A')],
            [new IntType(), new IntType()],
            [new IterableType(), new IterableType()],
            [
                new ObjectType(),
                version_compare(PHP_VERSION, '7.2', '<') ? null : new ObjectType()
            ],
            [new ResourceType(), null],
            [new StringType(), new StringType()],
            [new CallableType(), null, true],
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
        bool $givenIsProp = false
    ): void {
        $actualFnType = TypeConverter::toExampleFnType($givenDocType, $givenIsProp);

        self::assertEquals($expectedFnType, $actualFnType);
    }
}
