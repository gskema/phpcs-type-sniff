<?php

namespace Gskema\TypeSniff\Core\Type;

use Gskema\TypeSniff\Core\Type\Common\ArrayType;
use Gskema\TypeSniff\Core\Type\Common\BoolType;
use Gskema\TypeSniff\Core\Type\Common\CallableType;
use Gskema\TypeSniff\Core\Type\Common\FalseType;
use Gskema\TypeSniff\Core\Type\Common\FloatType;
use Gskema\TypeSniff\Core\Type\Common\FqcnType;
use Gskema\TypeSniff\Core\Type\Common\IntersectionType;
use Gskema\TypeSniff\Core\Type\Common\IntType;
use Gskema\TypeSniff\Core\Type\Common\IterableType;
use Gskema\TypeSniff\Core\Type\Common\MixedType;
use Gskema\TypeSniff\Core\Type\Common\NeverType;
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
            ['never'    , new NeverType()],

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
            ['string|int', new UnionType([new StringType(), new IntType()])],
            [
                'string[]|int|null',
                new UnionType([
                    new TypedArrayType(new StringType(), 1),
                    new IntType(),
                    new NullType(),
                ]),
            ],
            [
                'string|NodeList|Node|(Node|Location)[]',
                new UnionType([
                    new StringType(),
                    new FqcnType('NodeList'),
                    new FqcnType('Node'),
                    new TypedArrayType(new UnionType([
                        new FqcnType('Node'),
                        new FqcnType('Location')
                    ]), 1),
                ]),
            ],
            [
                'array<string,array<string,string>>|\Zend\ServiceManager\Config',
                new UnionType([
                    new TypedArrayType(new MixedType(), 1),
                    new FqcnType('\Zend\ServiceManager\Config'),
                ]),
            ],
            [
                'array<string,array<string,(int[]|string)[])>>',
                new TypedArrayType(new MixedType(), 1),
            ],
            [
                '(int[]|string)[]|null',
                new UnionType([
                    new TypedArrayType(new UnionType([
                        new TypedArrayType(new IntType(), 1),
                        new StringType(),
                    ]), 1),
                    new NullType(),
                ])
            ],
            [
                'array<int>|',
                new TypedArrayType(new MixedType(), 1),
            ],
            [
                '((bool|int)[][]|(string|float)[])[]',
                new TypedArrayType(new UnionType([
                    new TypedArrayType(new UnionType([new BoolType(), new IntType()]), 2), // (bool|int)[][]
                    new TypedArrayType(new UnionType([new StringType(), new FloatType()]), 1), // (string|float)[]
                ]), 1),
            ],
            ['[]', new TypedArrayType(new UndefinedType(), 1)],
            ['[][]', new TypedArrayType(new UndefinedType(), 2)],
            [
                '[]|null',
                new UnionType([
                    new TypedArrayType(new UndefinedType(), 1),
                    new NullType(),
                ]),
            ],
            ['Iterator&Countable', new IntersectionType([new FqcnType('Iterator'), new FqcnType('Countable')])],
            [
                'Iterator&Countable&Aggregator',
                new IntersectionType([new FqcnType('Iterator'), new FqcnType('Countable'), new FqcnType('Aggregator')])
            ],
            [
                '\Iterator&Countable&\Aggregator',
                new IntersectionType([new FqcnType('\Iterator'), new FqcnType('Countable'), new FqcnType('\Aggregator')])
            ],
            [
                '\Iterator&Countable&string', // allow parse but illegal
                new IntersectionType([new FqcnType('\Iterator'), new FqcnType('Countable'), new StringType()])
            ],
            [
                'string&int', // allow parse but illegal
                new IntersectionType([new StringType(), new IntType()])
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

    /**
     * @return mixed[][]
     */
    public function dataSplit(): array
    {
        return [
            ['int $param1', [['int'], '$param1']],
            ['int', [['int'], '']],
            ['int|null', [['int', 'null'], '']],
            ['int|null Desc', [['int', 'null'], 'Desc']],
            ['int |null Desc', [['int'], '|null Desc']],
            ['array<int, int> Desc', [['array<int, int>'], 'Desc']],
            ['int|array<int, int>|null Desc', [['int', 'array<int, int>', 'null'], 'Desc']],
            [
                'int|array<int|string, array<int, int|null>>|null Desc',
                [['int', 'array<int|string, array<int, int|null>>', 'null'], 'Desc']
            ],
            [' int|null    Desc a a a a', [['int', 'null'], 'Desc a a a a']],
            [
                'int|string|bool|array<int|string, array{int, string}>| Desc',
                [['int', 'string', 'bool', 'array<int|string, array{int, string}>'], 'Desc']
            ],
        ];
    }

    /**
     * @dataProvider dataSplit
     *
     * @param string  $givenTagBody
     * @param mixed[] $expectedSplit
     */
    public function testSplit(
        string $givenTagBody,
        array $expectedSplit,
    ): void {
        $proxy = new class extends TypeFactory {
            public static function doSplit(string $tagBody): array
            {
                return self::split($tagBody);
            }
        };
        $actualSplit = $proxy::doSplit($givenTagBody);

        self::assertEquals($expectedSplit, $actualSplit);
    }
}
