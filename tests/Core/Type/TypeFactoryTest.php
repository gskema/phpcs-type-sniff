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
    public function dataSplit(): array
    {
        $dataSets = [
            ['int $param1', [['int'], '$param1']],
            ['int', [['int'], '']],
            ['int |null', [['int', 'null'], '']],
            ['int | null Desc', [['int', 'null'], 'Desc']],
            ['int |null Desc', [['int', 'null'], 'Desc']],
            ['array<int, int> Desc', [['array<int, int>'], 'Desc']],
            ['int|array<int, int> | null Desc', [['int', 'array<int, int>', 'null'], 'Desc']],
            [
                'int|array<int | string, array<int, int|null>>|null Desc',
                [['int', 'array<int | string, array<int, int|null>>', 'null'], 'Desc']
            ],
            [' int|null    Desc a a a a', [['int', 'null'], 'Desc a a a a']],
            [
                'int|string | bool|array<int|string, array{int, string}>| Desc',
                [['int', 'string', 'bool', 'array<int|string, array{int, string}>', 'Desc'], null]
            ],
            [
              //             1
              // 01234567890 12345
                'int & string&bool',
                [['int & string&bool'], '']
            ],
            [
                '(int & string&bool) | bool Desc',
                [['(int & string&bool)', 'bool'], 'Desc']
            ],
            [ //            1         2         3         4         5
              // 0123456789 123456789 123456789 123456789 123456789 1
                'dog   |(int & string&bool)|  string|int  |  bool  Desc',
                [['dog', '(int & string&bool)', 'string', 'int', 'bool'], 'Desc']
            ],
            [
                '(int|array<int, string & int>) | bool & string|int|array(int, int) | Desc Desc2',
                [['(int|array<int, string & int>)', 'bool & string', 'int', 'array(int, int)', 'Desc'], 'Desc2'],
            ]
        ];

        return $dataSets;
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
