<?php

namespace Gskema\TypeSniff\Core\Type;

use PHPUnit\Framework\TestCase;
use Gskema\TypeSniff\Core\Type\Common\ArrayType;
use Gskema\TypeSniff\Core\Type\Common\BoolType;
use Gskema\TypeSniff\Core\Type\Common\CallableType;
use Gskema\TypeSniff\Core\Type\Common\FloatType;
use Gskema\TypeSniff\Core\Type\Common\FqcnType;
use Gskema\TypeSniff\Core\Type\Common\IntType;
use Gskema\TypeSniff\Core\Type\Common\IterableType;
use Gskema\TypeSniff\Core\Type\Common\ObjectType;
use Gskema\TypeSniff\Core\Type\Common\ResourceType;
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
    public function dataToExpectedDocType(): array
    {
        return [
            [new ArrayType(), null],
            [new BoolType(), new BoolType()],
            [new CallableType(), new CallableType()],
            [new FloatType(), new FloatType()],
            [new FqcnType('A'), new FqcnType('A')],
            [new IntType(), new IntType()],
            [new IterableType(), new IterableType()],
            [new ObjectType(), new ObjectType()],
            [new ResourceType(), new ResourceType()],
            [new SelfType(), null],
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
     * @dataProvider dataToExpectedDocType
     * @param TypeInterface      $givenFnType
     * @param TypeInterface|null $expectedDocType
     */
    public function testToExpectedDocType(
        TypeInterface $givenFnType,
        ?TypeInterface $expectedDocType
    ): void {
        $actualDocType = TypeConverter::toExpectedDocType($givenFnType);

        $this->assertEquals($expectedDocType, $actualDocType);
    }

    /**
     * @return TypeInterface[][]
     */
    public function dataToFunctionType(): array
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
            [new NullType(), null],
            [new SelfType(), null],
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
            [new ResourceType(), new ResourceType()],
            [new StringType(), new StringType()],
        ];
    }

    /**
     * @dataProvider dataToFunctionType
     * @param TypeInterface      $givenDocType
     * @param TypeInterface|null $expectedFnType
     */
    public function testToFunctionType(TypeInterface $givenDocType, ?TypeInterface $expectedFnType): void
    {
        $actualFnType = TypeConverter::toFunctionType($givenDocType);

        self::assertEquals($expectedFnType, $actualFnType);
    }
}
