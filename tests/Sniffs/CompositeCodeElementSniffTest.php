<?php

namespace Gskema\TypeSniff\Sniffs;

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\LocalFile;
use PHP_CodeSniffer\Ruleset;
use PHPUnit\Framework\TestCase;

class CompositeCodeElementSniffTest extends TestCase
{
    /**
     * @return mixed[][]
     */
    public function dataProcess(): array
    {
        $dataSets = [];

        // #0
        $dataSets[] = [
            [
                'addViolationId' => false,
                'FqcnMethodSniff.enabled' => 'true',
                'FqcnMethodSniff.reportMissingTags' => true,
            ],
            __DIR__ . '/fixtures/TestClass0.php',
            [
                '009 Replace array type with typed array type in PHPDoc for C2 constant, .e.g.: "string[]" or "SomeClass[]". Use mixed[] for generic arrays. Correct array depth must be specified.',
                '009 Type hint "array" is not compatible with C2 constant value type',
                '009 Missing "int" type in C2 constant type hint',
                '012 Create PHPDoc with typed array type hint for C3 constant, .e.g.: "string[]" or "SomeClass[]". Correct array depth must be specified.',
                '017 Add type declaration for property $prop1 or create PHPDoc with type hint. Add default value or keep property in an uninitialized state.',
                '022 Add type declaration for property $prop2 or create PHPDoc with type hint. Add default value or keep property in an uninitialized state.',
                '024 Add type hint to @var tag for property $prop3',
                '025 Add type declaration for property $prop3 or create PHPDoc with type hint. Add default value or keep property in an uninitialized state.',
                '027 Replace array type with typed array type in PHPDoc for property $prop4, .e.g.: "string[]" or "SomeClass[]". Use mixed[] for generic arrays. Correct array depth must be specified.',
                '028 Add type declaration for property $prop4, e.g.: "array". Add default value or keep property in an uninitialized state.',
                '030 Replace array type with typed array type in PHPDoc for property $prop5, .e.g.: "string[]" or "SomeClass[]". Use mixed[] for generic arrays. Correct array depth must be specified.',
                '033 Missing "array" type in property $prop6 type hint',
                '034 Add type declaration for property $prop6, e.g.: "string".',
                '037 Add type declaration for property $prop7, e.g.: "array".',
                '039 Remove property name $prop8 from @var tag',
                '040 Add type declaration for property $prop8, e.g.: "int".',
                '043 Add type declaration for property $prop9, e.g.: "array".',
                '045 Replace array type with typed array type in PHPDoc for property $prop10, .e.g.: "string[]" or "SomeClass[]". Use mixed[] for generic arrays. Correct array depth must be specified.',
                '045 Remove redundant property $prop10 type hints "array"',
                '046 Add type declaration for property $prop10, e.g.: "array".',
                '051 Replace array type with typed array type in PHPDoc for C6 constant, .e.g.: "string[]" or "SomeClass[]". Use mixed[] for generic arrays. Correct array depth must be specified.',
                '051 Remove redundant C6 constant type hints "array"',
                '054 Use a more specific type in typed array hint "array[]" for C7 constant. Correct array depth must be specified.',
                '057 Use a more specific type in typed array hint "array[][]" for property $prop11. Correct array depth must be specified.',
                '058 Add type declaration for property $prop11, e.g.: "array".',
            ],
        ];

        // #1
        $dataSets[] = [
            [
                'addViolationId' => false,
                'FqcnMethodSniff.invalidTags' => ['@SmartTemplate'],
                'FqcnMethodSniff.reportMissingTags' => true,
            ],
            __DIR__ . '/fixtures/TestClass1.php',
            [
                '007 Add type declaration for parameter $a or create PHPDoc with type hint.',
                '007 Create PHPDoc with typed array type hint for parameter $b, .e.g.: "string[]" or "SomeClass[]". Correct array depth must be specified.',
                '012 Add type hint in PHPDoc tag for parameter $c',
                '014 Add type declaration for parameter $c or create PHPDoc with type hint.',
                '014 Add type declaration for return value or create PHPDoc with type hint.',
                '014 Missing PHPDoc tag or void type declaration for return value',
                '019 Add type hint in PHPDoc tag for parameter $d',
                '020 Add type hint in PHPDoc tag for parameter $e, e.g. "int"',
                '021 Add type hint in PHPDoc tag for parameter $f, e.g. "SomeClass[]"',
                '024 Replace array type with typed array type in PHPDoc for return value, .e.g.: "string[]" or "SomeClass[]". Use mixed[] for generic arrays. Correct array depth must be specified.',
                '026 Add type declaration for parameter $d or create PHPDoc with type hint.',
                '026 Add type declaration for parameter $g, e.g.: "mixed".',
                '031 Change type hint for parameter $h to union, e.g. SomeClass|null',
                '035 Add type declaration for parameter $h, e.g.: "?SomeClass".',
                '035 Add type declaration for parameter $i, e.g.: "?int".',
                '035 Add type declaration for return value, e.g.: "?array".',
                '040 Replace array type with typed array type in PHPDoc for parameter $j, .e.g.: "string[]" or "SomeClass[]". Use mixed[] for generic arrays. Correct array depth must be specified.',
                '040 Remove redundant parameter $j type hints "array"',
                '041 Replace array type with typed array type in PHPDoc for parameter $k, .e.g.: "string[]" or "SomeClass[]". Use mixed[] for generic arrays. Correct array depth must be specified.',
                '042 Add type hint in PHPDoc tag for parameter $l, e.g. "SomeClass[]"',
                '044 Missing "null" type in parameter $n type hint',
                '045 Type hint "string" is not compatible with parameter $o value type',
                '045 Missing "int" type in parameter $o type hint',
                '046 Type hint "int" is not compatible with parameter $p value type',
                '046 Missing "null, string" types in parameter $p type hint',
                '065 Useless PHPDoc',
                '070 Useless tag',
                '087 Add type declaration for property $prop1 or create PHPDoc with type hint. Add default value or keep property in an uninitialized state.',
            ],
        ];

        // #2
        $dataSets[] = [
            [
                'addViolationId' => false,
                'FqcnMethodSniff.enabled' => 'false',
                'FqcnMethodSniff.reportMissingTags' => true,
            ],
            __DIR__ . '/fixtures/TestClass1.php',
            [
                '087 Add type declaration for property $prop1 or create PHPDoc with type hint. Add default value or keep property in an uninitialized state.'
            ],
        ];

        // #3
        $dataSets[] = [
            [
                'addViolationId' => false,
                'useReflection' => true,
                'FqcnMethodSniff.reportMissingTags' => true,
            ],
            __DIR__ . '/fixtures/TestClass3.php',
            [
                '007 Add type declaration for parameter $arg1 or create PHPDoc with type hint.',
                '022 Missing @inheritDoc tag. Remove duplicated parent PHPDoc content.',
                '027 Type hint "string" is not compatible with parameter $arg1 value type',
                '027 Missing "null, int" types in parameter $arg1 type hint',
                '042 Type hints "string, float" are not compatible with return value value type',
                '042 Missing "null, int" types in return value type hint',
                '065 Type hint "string" is not compatible with return value value type',
                '072 Type hint "string" is not compatible with return value value type',
            ],
        ];

        // #4
        $dataSets[] = [
            [
                'addViolationId' => false,
                'useReflection' => false,
                'FqcnMethodSniff.reportMissingTags' => true,
            ],
            __DIR__ . '/fixtures/TestClass4.php',
            [
                '008 Replace array type with typed array type in PHPDoc for parameter $arg1, .e.g.: "string[]" or "SomeClass[]". Use mixed[] for generic arrays. Correct array depth must be specified.',
                '037 Type hint "static" is not compatible with return value value type',
                '037 Missing "self" type in return value type hint',
                '094 Type hint "$this" is not compatible with return value value type',
                '094 Missing "self" type in return value type hint',
                '102 Remove @return void tag, not necessary',
                '111 Useless PHPDoc',
                '117 Use a more specific type in typed array hint "array[]" for parameter $arg2. Correct array depth must be specified.',
                '124 Type hint "mixed" is not compatible with parameter $arg1 value type',
                '126 Type hint "mixed" is not compatible with return value value type',
                '135 Add type declaration for return value, e.g.: "mixed".',
            ],
        ];

        // #5
        $dataSets[] = [
            [
                'addViolationId' => false,
                'useReflection' => false,
                'FqcnMethodSniff.reportMissingTags' => true,
            ],
            __DIR__ . '/fixtures/TestClass5.php',
            [
                '006 Useless description',
                '007 Useless tag',
                '012 Useless description.',
                '023 Change parameter $arg1 type declaration to nullable, e.g. ?string. Remove default null value if this argument is required.',
                '035 Remove redundant parameter $arg7 type hints "double"',
                '036 Remove redundant parameter $arg8 type hints "double"',
                '037 Remove redundant parameter $arg9 type hints "double"',
                '042 Add type declaration for parameter $arg1, e.g.: "float".',
                '045 Add type declaration for parameter $arg4, e.g.: "float".',
                '054 Change parameter $arg1 type declaration to nullable, e.g. ?string. Remove default null value if this argument is required.',
                '063 Add type declaration for parameter $default, e.g.: "mixed".',
                '063 Add type declaration for return value, e.g.: "mixed".',
                '070 Change type hint for parameter $arg1 to union, e.g. SomeClass|null',
                '074 Add type declaration for parameter $arg1, e.g.: "?SomeClass".',
                '074 Add type declaration for return value, e.g.: "mixed".',
                '078 Create PHPDoc with typed array type hint for parameter $arg1, .e.g.: "string[]|null" or "SomeClass[]|null". Correct array depth must be specified.',
                '083 Useless PHPDoc',
                '086 Useless PHPDoc',
                '089 Add type declaration for property $prop1, e.g.: "?SomeClass". Add default value or keep property in an uninitialized state.',
                '092 Use void return value type declaration or change type to union, e.g. SomeClass|null',
                '094 Add type declaration for return value, e.g.: "?SomeClass".',
                '101 Useless PHPDoc',
                '107 Use a more specific type in typed array hint "[]" for parameter $arg1. Correct array depth must be specified.',
                '118 Replace array type with typed array type in PHPDoc for C4 constant, .e.g.: "string[]" or "SomeClass[]". Use mixed[] for generic arrays. Correct array depth must be specified.',
                '121 Use a more specific type in typed array hint "[]" for C5 constant. Correct array depth must be specified.',
                '124 Use a more specific type in typed array hint "[][]" for C6 constant. Correct array depth must be specified.',
                '124 Type hint "null" is not compatible with C6 constant value type',
                '127 Use a more specific type in typed array hint "[][]" for property $prop2. Correct array depth must be specified.',
                '128 Add type declaration for property $prop2, e.g.: "?array".',
            ],
        ];

        // #6
        $dataSets[] = [
            [
                'addViolationId' => false,
                'useReflection'                     => false,
                'FqcnMethodSniff.reportMissingTags' => false,
            ],
            __DIR__ . '/fixtures/TestClass6.php',
            [
                '020 Add type declaration for property $prop2, e.g.: "array".',
                '023 Add type declaration for property $prop3, e.g.: "array".',
                '026 Add type declaration for property $prop4, e.g.: "array".',
                '064 Add type declaration for parameter $arg1 or create PHPDoc with type hint.',
                '064 Add typed array type hint for parameter $arg2, .e.g.: "string[]" or "SomeClass[]". Correct array depth must be specified.',
                '064 Add type declaration for return value or create PHPDoc with type hint.',
                '069 Add type declaration for property $prop1, e.g.: "?string". Add default value or keep property in an uninitialized state.',
                '072 Returned property $prop1 is nullable, add null return doc type, e.g. null|string',
                '074 Returned property $prop1 is nullable, use nullable return type declaration, e.g. ?string',
                '088 Add type declaration for property $prop5 or create PHPDoc with type hint. Add default value or keep property in an uninitialized state.',
                '096 Add type declaration for property $prop6, e.g.: "int". Add default value or keep property in an uninitialized state.',
            ],
        ];

        // #7
        $dataSets[] = [
            [
                'addViolationId' => false,
                'useReflection' => false,
                'FqcnMethodSniff.reportMissingTags' => true,
            ],
            __DIR__ . '/fixtures/TestTrait7.php',
            [
                '007 Add type declaration for property $prop1 or create PHPDoc with type hint. Add default value or keep property in an uninitialized state.',
            ],
        ];

        // #8
        $dataSets[] = [
            [
                'addViolationId' => false,
                'useReflection' => false,
                'FqcnMethodSniff.reportMissingTags' => true,
            ],
            __DIR__ . '/fixtures/TestInterface8.php',
            [
                '015 Add type declaration for return value or create PHPDoc with type hint.',
            ],
        ];

        // #9
        $dataSets[] = [
            [
                'addViolationId' => false,
                'useReflection' => false,
                'FqcnMethodSniff.reportMissingTags' => true,
            ],
            __DIR__ . '/fixtures/TestClass7.php',
            [
                '008 Add type declaration for property $prop1, e.g.: "int". Add default value or keep property in an uninitialized state.',
                '011 Add type declaration for property $prop2, e.g.: "int". Add default value or keep property in an uninitialized state.',
                '014 Add type declaration for property $prop3, e.g.: "int". Add default value or keep property in an uninitialized state.',
                '017 Add type declaration for property $prop4, e.g.: "int". Add default value or keep property in an uninitialized state.',
            ],
        ];

        // #10
        $dataSets[] = [
            [
                'addViolationId' => false,
                'useReflection' => false,
                'FqcnMethodSniff.reportMissingTags' => true,
            ],
            __DIR__ . '/fixtures/TestClass8.php',
            [
                '010 Add type declaration for property $foo, e.g.: "?string". Add default value or keep property in an uninitialized state.',
                '020 Add type hint in PHPDoc tag for return value, e.g. "null|string"',
                '020 Returned property $foo is nullable, add null return doc type, e.g. null|string',
            ],
        ];

        // #11
        $dataSets[] = [
            [
                'addViolationId' => false,
                'useReflection'                     => false,
                'FqcnMethodSniff.reportMissingTags' => false,
            ],
            __DIR__ . '/fixtures/TestClass8.php',
            [
                '010 Add type declaration for property $foo, e.g.: "?string". Add default value or keep property in an uninitialized state.',
            ],
        ];

        // #12
        $dataSets[] = [
            [
                'useReflection' => false,
                'addViolationId' => true,
                'FqcnMethodSniff.reportMissingTags' => true,
            ],
            __DIR__ . '/fixtures/TestClass5.php',
            [
                '006 Useless description [7f23a1e9428db071]',
                '007 Useless tag [2ea1f782b8a5307c]',
                '012 Useless description. [85953b0ff830ae61]',
                '023 Change parameter $arg1 type declaration to nullable, e.g. ?string. Remove default null value if this argument is required. [cf5f1d5c5b01ed6e]',
                '035 Remove redundant parameter $arg7 type hints "double" [1312f1309767c2a9]',
                '036 Remove redundant parameter $arg8 type hints "double" [dd763f4a2ffdd9ca]',
                '037 Remove redundant parameter $arg9 type hints "double" [e980b13da4c9e8c9]',
                '042 Add type declaration for parameter $arg1, e.g.: "float". [bdee84f416f32c43]',
                '045 Add type declaration for parameter $arg4, e.g.: "float". [ace615cc4193b56d]',
                '054 Change parameter $arg1 type declaration to nullable, e.g. ?string. Remove default null value if this argument is required. [b0d14463d8e6717f]',
                '063 Add type declaration for parameter $default, e.g.: "mixed". [3fe4e363760bc40d]',
                '063 Add type declaration for return value, e.g.: "mixed". [85504af2efcc134d]',
                '070 Change type hint for parameter $arg1 to union, e.g. SomeClass|null [15e2f9eddf827325]',
                '074 Add type declaration for parameter $arg1, e.g.: "?SomeClass". [d6bfc821faa4a339]',
                '074 Add type declaration for return value, e.g.: "mixed". [bacd054cd6046b0e]',
                '078 Create PHPDoc with typed array type hint for parameter $arg1, .e.g.: "string[]|null" or "SomeClass[]|null". Correct array depth must be specified. [6e7fdf0bab1792de]',
                '083 Useless PHPDoc [81dfff54c0c60d71]',
                '086 Useless PHPDoc [b3d61e873dc52dd3]',
                '089 Add type declaration for property $prop1, e.g.: "?SomeClass". Add default value or keep property in an uninitialized state. [96581e1f88581ab2]',
                '092 Use void return value type declaration or change type to union, e.g. SomeClass|null [42e6bbb7923a8dd1]',
                '094 Add type declaration for return value, e.g.: "?SomeClass". [25d1eef607958faf]',
                '101 Useless PHPDoc [390da7987aaad158]',
                '107 Use a more specific type in typed array hint "[]" for parameter $arg1. Correct array depth must be specified. [32f13f1f2c0119d7]',
                '118 Replace array type with typed array type in PHPDoc for C4 constant, .e.g.: "string[]" or "SomeClass[]". Use mixed[] for generic arrays. Correct array depth must be specified. [629a111a8a1d4ead]',
                '121 Use a more specific type in typed array hint "[]" for C5 constant. Correct array depth must be specified. [2e6b8365a803aeed]',
                '124 Use a more specific type in typed array hint "[][]" for C6 constant. Correct array depth must be specified. [8a453fe54b051f80]',
                '124 Type hint "null" is not compatible with C6 constant value type [79c7cfa740ac5627]',
                '127 Use a more specific type in typed array hint "[][]" for property $prop2. Correct array depth must be specified. [2987854751394f1d]',
                '128 Add type declaration for property $prop2, e.g.: "?array". [fd2210f6d65edbe6]',
            ],
        ];

        // #13
        $dataSets[] = [
            [
                'addViolationId' => false,
                'useReflection'                     => false,
                'FqcnMethodSniff.reportMissingTags' => true,
            ],
            __DIR__ . '/fixtures/TestClass9.php',
            [
                '006 Useless description',
                '008 Useless tag',
                '025 Type hint "string" is not compatible with CONST3 constant value type',
                '025 Missing "array" type in CONST3 constant type hint',
                '031 Type hint "null" is not compatible with CONST4 constant value type',
                '037 Add type declaration for property $prop1, e.g.: "array".',
                '043 Add type declaration for property $prop2, e.g.: "array". Add default value or keep property in an uninitialized state.',
                '050 Add type declaration for property $prop3, e.g.: "?array". Add default value or keep property in an uninitialized state.',
                '053 Add type declaration for return value, e.g.: "array".',
                '063 Add type hint in PHPDoc tag for parameter $a, e.g. "int"',
                '063 Missing PHPDoc tag for parameter $a',
                '064 Add type hint in PHPDoc tag for parameter $b, e.g. "null|string"',
                '064 Missing PHPDoc tag for parameter $b',
                '068 Add type declaration for parameter $d, e.g.: "array".',
                '069 Add type declaration for return value, e.g.: "array".',
                '073 Replace nullable type "?int" with union type with null "int|null" for property $prop4.',
                '074 Add type declaration for property $prop4, e.g.: "?int". Add default value or keep property in an uninitialized state.',
                '076 Replace nullable type "?int|string" with union type with null "int|string|null" for property $prop5.',
                '080 Replace nullable type "?string" with union type with null "string|null" for parameter $a.',
                '080 Type hint "?string" is not compatible with parameter $a value type',
                '080 Missing "null, string" types in parameter $a type hint',
                '081 Replace nullable type "?int" with union type with null "int|null" for return value.',
                '081 Type hint "?int" is not compatible with return value value type',
                '081 Missing "null, int" types in return value type hint',
                '088 Type declaration of return value not compatible with ArrayShape attribute',
            ],
        ];

        // #14
        $dataSets[] = [
            [
                'addViolationId' => false,
                'useReflection' => false,
                'reportType' => 'error',
                'FqcnMethodSniff.reportMissingTags' => true,
            ],
            __DIR__ . '/fixtures/TestInterface8.php',
            [], // 1 error reported, 0 warnings
        ];

        // #15
        $dataSets[] = [
            [
                'addViolationId' => false,
                'useReflection' => false,
                'FqcnMethodSniff.reportMissingTags' => true,
            ],
            __DIR__ . '/fixtures/TestClass10.php',
            [
                '009 Add type declaration for property $prop1 or create PHPDoc with type hint. Add default value or keep property in an uninitialized state.',
                '011 Add type declaration for property $prop2, e.g.: "?SomeClass" or create PHPDoc with type hint.',
                '013 Add type declaration for property $prop3, e.g.: "int" or create PHPDoc with type hint.',
                '016 Add type declaration for property $prop4, e.g.: "string". Add default value or keep property in an uninitialized state.',
                '019 Add type declaration for property $prop5, e.g.: "?string". Add default value or keep property in an uninitialized state.',
                '024 Type hint "int" is not compatible with property $prop7 value type',
                '027 Type hint "int" is not compatible with property $prop8 value type',
                '027 Missing "null" type in property $prop8 type hint',
                '031 Useless PHPDoc',
            ],
        ];

        // #16
        $dataSets[] = [
            [
                'addViolationId' => false,
                'useReflection' => false,
                'FqcnMethodSniff.reportMissingTags' => true,
            ],
            __DIR__ . '/fixtures/TestClass11.php',
            [
                '009 Add type declaration for property $prop2, e.g.: "string" or create PHPDoc with type hint.',
            ],
        ];

        // #17
        $dataSets[] = [
            [
                'addViolationId' => false,
                'useReflection' => false,
                'FqcnMethodSniff.reportMissingTags' => true,
            ],
            __DIR__ . '/fixtures/TestClass12NoParent.php',
            [], // parent class not found, 007 error ignored
        ];

        // #18
        $dataSets[] = [
            [
                'addViolationId' => false,
                'useReflection' => false,
                'inspectPromotedConstructorPropertyAs' => 'prop'
            ],
            __DIR__ . '/fixtures/TestClass12.php',
            [
                '008 Add type hint in PHPDoc tag for parameter $prop1',
                '010 Replace array type with typed array type in PHPDoc for parameter $prop3, .e.g.: "string[]" or "SomeClass[]". Use mixed[] for generic arrays. Correct array depth must be specified.',
                '012 Promoted constructor property is configured to be documented using inline PHPDoc as prop, remove @param tag from __construct() PHPDoc block.',
                '013 Promoted constructor property is configured to be documented using inline PHPDoc as prop, remove @param tag from __construct() PHPDoc block.',
                '014 Promoted constructor property is configured to be documented using inline PHPDoc as prop, remove @param tag from __construct() PHPDoc block.',
                '015 Promoted constructor property is configured to be documented using inline PHPDoc as prop, remove @param tag from __construct() PHPDoc block.',
                '016 Promoted constructor property is configured to be documented using inline PHPDoc as prop, remove @param tag from __construct() PHPDoc block.',
                '019 Add type declaration for parameter $prop1 or create PHPDoc with type hint.',
                '023 Add type declaration for property $prop5 or create PHPDoc with type hint. Add default value or keep property in an uninitialized state.',
                '025 Create PHPDoc with typed array type hint for property $prop7, .e.g.: "string[]" or "SomeClass[]". Correct array depth must be specified.',
                '030 Create PHPDoc with typed array type hint for property $prop10, .e.g.: "string[]" or "SomeClass[]". Correct array depth must be specified.',
            ],
        ];

        // #19
        $dataSets[] = [
            [
                'addViolationId' => false,
                'useReflection' => false,
                'inspectPromotedConstructorPropertyAs' => 'param'
            ],
            __DIR__ . '/fixtures/TestClass12.php',
            [
                '008 Add type hint in PHPDoc tag for parameter $prop1',
                '010 Replace array type with typed array type in PHPDoc for parameter $prop3, .e.g.: "string[]" or "SomeClass[]". Use mixed[] for generic arrays. Correct array depth must be specified.',
                '012 Add type hint in PHPDoc tag for parameter $prop5',
                '014 Replace array type with typed array type in PHPDoc for parameter $prop7, .e.g.: "string[]" or "SomeClass[]". Use mixed[] for generic arrays. Correct array depth must be specified.',
                '016 Add type hint in PHPDoc tag for parameter $prop10, e.g. "SomeClass[]"',
                '019 Add type declaration for parameter $prop1 or create PHPDoc with type hint.',
                '023 Add type declaration for parameter $prop5 or create PHPDoc with type hint.',
                '026 Promoted constructor property is configured to be documented using __construct() PHPDoc block as param, remove inline @var tag',
                '028 Promoted constructor property is configured to be documented using __construct() PHPDoc block as param, remove inline @var tag',
                '029 Add typed array type hint for parameter $prop9, .e.g.: "string[]" or "SomeClass[]". Correct array depth must be specified.',
            ],
        ];

        // #20
        $dataSets[] = [
            [
                'addViolationId' => false,
                'useReflection' => false,
            ],
            __DIR__ . '/fixtures/TestClass13.php',
            [
                '010 Add type declaration for return value, e.g.: "static".',
                '023 Type hint "$this" is not compatible with return value value type',
                '023 Missing "self" type in return value type hint',
                '030 Type hint "$this" is not compatible with return value value type',
                '030 Missing "static" type in return value type hint',
                '039 Add type declaration for return value, e.g.: "self".',
                '046 Useless PHPDoc',
                '051 Type hint "self" is not compatible with return value value type',
                '051 Missing "static" type in return value type hint',
                '060 Add type declaration for return value, e.g.: "static".',
                '065 Type hint "static" is not compatible with return value value type',
                '065 Missing "self" type in return value type hint',
                '074 Useless PHPDoc',
            ],
        ];

        return $dataSets;
    }

    /**
     * @dataProvider dataProcess
     *
     * @param mixed[]  $givenConfig
     * @param string   $givenPath
     * @param string[] $expectedWarnings
     */
    public function testProcess(
        array $givenConfig,
        string $givenPath,
        array $expectedWarnings,
    ): void {
        static::assertFileExists($givenPath);

        $givenFile = new LocalFile($givenPath, new Ruleset(new Config()), new Config());
        $givenFile->parse();

        $ref = './src/Sniffs/CompositeCodeElementSniff.php'; // @see phpcs.xml
        $givenFile->ruleset->ruleset[$ref] = [
            'properties' => $givenConfig,
        ];

        $sniff = new CompositeCodeElementSniff();
        $processedTokenCodes = array_flip($sniff->register()); // faster lookup

        foreach ($givenFile->getTokens() as $ptr => $token) {
            if (key_exists($token['code'], $processedTokenCodes)) {
                $sniff->process($givenFile, $ptr);
            }
        }

        $actualWarnings = [];
        foreach ($givenFile->getWarnings() as $line => $colWarnings) {
            foreach ($colWarnings as $warnings) {
                foreach ($warnings as $warning) {
                    $lineKey = str_pad($line, '3', '0', STR_PAD_LEFT);
                    $actualWarnings[$line][] = $lineKey . ' ' . $warning['message'];
                }
            }
        }

        // Elements are iterated by type first, then line. Need to resort to match test files.
        ksort($actualWarnings);
        $actualWarnings = $actualWarnings ? array_merge(...$actualWarnings) : [];

        static::assertEquals($expectedWarnings, $actualWarnings);
    }
}
