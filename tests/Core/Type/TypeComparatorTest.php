<?php

namespace Gskema\TypeSniff\Core\Type;

use PHPUnit\Framework\TestCase;

class TypeComparatorTest extends TestCase
{
    /**
     * @return string[][]
     */
    public function dataCompare(): array
    {
        return [
            // covered types
             0 => ['array',  'array',    '', '', ''],
             1 => ['array',  'iterable', '', '', ''],
             2 => ['Fqcn',   'Fqcn',     '', '', ''],
             3 => ['Fqcn',   'callable', '', '', ''],
             4 => ['Fqcn',   'iterable', '', '', ''],
             5 => ['Fqcn',   'object',   '', '', ''],
             6 => ['Fqcn',   'parent',   '', '', ''],
             7 => ['Fqcn',   'self',     '', '', ''],
             8 => ['parent', 'parent',   '', '', ''],
             9 => ['parent', 'callable', '', '', ''],
            10 => ['parent', 'Fqcn',     '', '', ''],
            11 => ['parent', 'iterable', '', '', ''],
            12 => ['parent', 'object',   '', '', ''],
            13 => ['self',   'self',     '', '', ''],
            14 => ['self',   'callable', '', '', ''],
            15 => ['self',   'Fqcn',     '', '', ''],
            16 => ['self',   'iterable', '', '', ''],
            17 => ['self',   'object',   '', '', ''],
            18 => ['double', 'double',   '', '', ''],
            19 => ['double', 'float',    '', '', ''],
            20 => ['false',  'false',    '', '', ''],
            21 => ['false',  'bool',     '', '', ''],
            22 => ['static', 'static',   '', '', ''],
            23 => ['static', 'static',   '', '', ''],
            24 => ['static', 'callable', '', '', ''],
            25 => ['static', 'Fqcn',     '', '', ''],
            26 => ['static', 'iterable', '', '', ''],
            27 => ['static', 'object',   '', '', ''],
            28 => ['static', 'parent',   '', '', ''],
            29 => ['true',   'true',     '', '', ''],
            30 => ['true',   'bool',     '', '', ''],
            31 => ['int[]',  'int[]',    '', '', ''],
            32 => ['int[]',  'array',    '', '', ''],

            // missing types
            33 => ['', 'array',  '', '', 'array' ],
            34 => ['', 'Fqcn',   '', '', 'Fqcn'  ],
            35 => ['', 'parent', '', '', 'parent'],
            36 => ['', 'self',   '', '', 'self'  ],
            37 => ['', 'double', '', '', 'double'],
            38 => ['', 'false',  '', '', 'false' ],
            39 => ['', 'true',   '', '', 'true'  ],

            // wrong types
            40 => ['int|array',  'array',  '', 'int', ''],
            41 => ['int|Fqcn',   'Fqcn',   '', 'int', ''],
            42 => ['int|parent', 'parent', '', 'int', ''],
            43 => ['int|self',   'self',   '', 'int', ''],
            44 => ['int|double', 'double', '', 'int', ''],
            45 => ['int|false',  'false',  '', 'int', ''],
            46 => ['int|true',   'true',   '', 'int', ''],

            // wrong + missing types
            47 => ['int', 'array',  '', 'int', 'array' ],
            48 => ['int', 'Fqcn',   '', 'int', 'Fqcn'  ],
            49 => ['int', 'parent', '', 'int', 'parent'],
            50 => ['int', 'self',   '', 'int', 'self'  ],
            51 => ['int', 'double', '', 'int', 'double'],
            52 => ['int', 'false',  '', 'int', 'false' ],
            53 => ['int', 'true',   '', 'int', 'true'  ],

            // other cases
            54 => ['int[]|array', '',         '', '',             ''   ],
            55 => ['int[]|array', 'array',    '', '',             ''   ],
            56 => ['int[]|array', 'iterable', '', '',             ''   ],
            57 => ['int[]|array', 'int',      '', 'int[], array', 'int'],

            58 => ['float', 'float', 'int', '', ''],
            59 => ['double', 'float', 'int', '', ''],
            60 => ['double', 'float', 'int', '', ''],
        ];
    }

    /**
     * @dataProvider dataCompare
     *
     * @param string      $givenRawDocType
     * @param string      $givenRawFnType
     * @param string|null $givenRawValueType
     * @param string      $expectedWrongRawDocTypes
     * @param string      $expectedMissingRawDocTypes
     */
    public function testCompare(
        string $givenRawDocType,
        string $givenRawFnType,
        ?string $givenRawValueType,
        string $expectedWrongRawDocTypes,
        string $expectedMissingRawDocTypes
    ): void {
        $givenDocType = TypeFactory::fromRawType($givenRawDocType);
        $givenFnType = TypeFactory::fromRawType($givenRawFnType);
        $givenValType = TypeFactory::fromRawType($givenRawValueType);

        [$actualWrongDocTypes, $actualMissingDocTypes] = TypeComparator::compare($givenDocType, $givenFnType, $givenValType);

        static::assertEquals($expectedWrongRawDocTypes, $this->implodeTypes($actualWrongDocTypes));
        static::assertEquals($expectedMissingRawDocTypes, $this->implodeTypes($actualMissingDocTypes));
    }

    /**
     * @param TypeInterface[] $types
     *
     * @return string
     */
    protected function implodeTypes(array $types): string
    {
        $rawTypes = [];
        foreach ($types as $type) {
            $rawTypes[] = $type->toString();
        }

        return implode(', ', $rawTypes);
    }
}
