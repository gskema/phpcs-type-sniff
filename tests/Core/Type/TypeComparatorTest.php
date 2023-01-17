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
        // doc_type, fn_type, val_type, wrong_doc, missing_doc
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

            // wrong types
            33 => ['int|array',  'array',  '', 'int', ''],
            34 => ['int|Fqcn',   'Fqcn',   '', 'int', ''],
            35 => ['int|parent', 'parent', '', 'int', ''],
            36 => ['int|self',   'self',   '', 'int', ''],
            37 => ['int|double', 'double', '', 'int', ''],
            38 => ['int|false',  'false',  '', 'int', ''],
            39 => ['int|true',   'true',   '', 'int', ''],

            // wrong + missing types
            40 => ['int', 'array',  '', 'int', 'array' ],
            41 => ['int', 'Fqcn',   '', 'int', 'Fqcn'  ],
            42 => ['int', 'parent', '', 'int', 'parent'],
            43 => ['int', 'self',   '', 'int', 'self'  ],
            44 => ['int', 'double', '', 'int', 'double'],
            45 => ['int', 'false',  '', 'int', 'false' ],
            46 => ['int', 'true',   '', 'int', 'true'  ],

            // other cases
            47 => ['int[]|array', '',         '', '',             ''   ],
            48 => ['int[]|array', 'array',    '', '',             ''   ],
            49 => ['int[]|array', 'iterable', '', '',             ''   ],
            50 => ['int[]|array', 'int',      '', 'int[], array', 'int'],

            51 => ['float', 'float', 'int', '', ''],
            52 => ['double', 'float', 'int', '', ''],
            53 => ['double', 'float', 'int', '', ''],
            54 => ['', 'float', '', '', ''],
            55 => ['bool', 'float', null, '', 'float'],

            56 => ['mixed|null', 'mixed', '', '', ''],
            57 => ['int|null', 'mixed', '', '', 'mixed'],
            58 => ['int|float|null', 'int|string', '', 'float, null', 'string'],

            59 => ['int|null', 'int|null|false', '', '', 'false'],
            60 => ['int|null|bool', 'int|null|false', '', 'bool', 'false'],
            61 => ['bool', 'bool', 'false', '', ''],
            62 => ['bool', 'bool', 'true', '', ''],
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
        string $expectedMissingRawDocTypes,
    ): void {
        $givenDocType = TypeFactory::fromRawType($givenRawDocType);
        $givenFnType = TypeFactory::fromRawType($givenRawFnType);
        $givenValType = null !== $givenRawValueType ? TypeFactory::fromRawType($givenRawValueType) : null;

        [$actualWrongDocTypes, $actualMissingDocTypes] = TypeComparator::compare($givenDocType, $givenFnType, $givenValType, false);

        static::assertEquals($expectedWrongRawDocTypes, $this->implodeTypes($actualWrongDocTypes), 'Wrong doc types');
        static::assertEquals($expectedMissingRawDocTypes, $this->implodeTypes($actualMissingDocTypes), 'Missing doc types');
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
