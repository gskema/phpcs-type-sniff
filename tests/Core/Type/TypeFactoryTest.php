<?php

namespace Gskema\TypeSniff\Core\Type;

use Gskema\TypeSniff\Core\Type\DocBlock\ResourceType;
use PHPUnit\Framework\TestCase;
use Gskema\TypeSniff\Core\Type\Common\ArrayType;
use Gskema\TypeSniff\Core\Type\Common\BoolType;
use Gskema\TypeSniff\Core\Type\Common\CallableType;
use Gskema\TypeSniff\Core\Type\Common\FloatType;
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

class TypeFactoryTest extends TestCase
{
    /**
     * @return mixed[][]
     */
    public function dataFromRawType(): array
    {
        return [
            ['array'    , new ArrayType()],
            ['bool'     , new BoolType()],
            ['boolean'  , new BoolType()],
            ['callable' , new CallableType()],
            ['double'   , new DoubleType()],
            ['false'    , new FalseType()],
            ['float'    , new FloatType()],
            ['int'      , new IntType()],
            ['integer'  , new IntType()],
            ['iterable' , new IterableType()],
            ['mixed'    , new MixedType()],
            ['null'     , new NullType()],
            ['object'   , new ObjectType()],
            ['resource' , new ResourceType()],
            ['self'     , new SelfType()],
            ['static'   , new StaticType()],
            ['string'   , new StringType()],
            ['$this'    , new ThisType()],
            ['true'     , new TrueType()],
            [''         , new UndefinedType()],
            ['void'     , new VoidType()],

            ['?array'    , new NullableType(new ArrayType())],
            ['?bool'     , new NullableType(new BoolType())],
            ['?callable' , new NullableType(new CallableType())],
            ['?float'    , new NullableType(new FloatType())],
            ['?int'      , new NullableType(new IntType())],
            ['?iterable' , new NullableType(new IterableType())],
            ['?object'   , new NullableType(new ObjectType())],
            ['?resource' , new NullableType(new ResourceType())],
            ['?self'     , new NullableType(new SelfType())],
            ['?string'   , new NullableType(new StringType())],

            ['string[]'  , new TypedArrayType(new StringType(), 1)],
            ['string|int', new CompoundType([new StringType(), new IntType()])],
            [
                'string[]|int|null',
                new CompoundType([
                    new TypedArrayType(new StringType(), 1),
                    new IntType(),
                    new NullType()
                ])
            ],
        ];
    }

    /**
     * @dataProvider dataFromRawType
     * @param string        $givenRawType
     * @param TypeInterface $expectedType
     */
    public function testFromRawType(string $givenRawType, TypeInterface $expectedType): void
    {
        $actualType = TypeFactory::fromRawType($givenRawType);

        self::assertEquals($expectedType, $actualType);
    }
}
