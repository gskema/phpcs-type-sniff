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
        $php74 = version_compare(PHP_VERSION, '7.4', '>=');

        $dataSets = [];

        // #0
        $dataSets[] = [
            [
                'FqcnMethodSniff.enabled' => 'true',
            ],
            __DIR__ . '/fixtures/TestClass0.php',
            [
                '009 Replace array type with typed array type in PHPDoc for C2 constant, .e.g.: "string[]" or "SomeClass[]". Use mixed[] for generic arrays. Correct array depth must be specified.',
                '009 Type hint "array" is not compatible with C2 constant value type',
                '009 Missing "int" type in C2 constant type hint',
                '012 Create PHPDoc with typed array type hint for C3 constant, .e.g.: "string[]" or "SomeClass[]". Correct array depth must be specified.',
                '017 Add @var tag for property $prop1',
                $php74 ? '' : '017 Property $prop1 not initialized by __construct(), add null doc type or set a default value',
                '022 Add @var tag for property $prop2',
                $php74 ? '' : '022 Property $prop2 not initialized by __construct(), add null doc type or set a default value',
                '024 Add type hint to @var tag for property $prop3',
                $php74 ? '' : '024 Property $prop3 not initialized by __construct(), add null doc type or set a default value',
                '027 Replace array type with typed array type in PHPDoc for property $prop4, .e.g.: "string[]" or "SomeClass[]". Use mixed[] for generic arrays. Correct array depth must be specified.',
                $php74 ? '' : '027 Property $prop4 not initialized by __construct(), add null doc type or set a default value',
                '030 Replace array type with typed array type in PHPDoc for property $prop5, .e.g.: "string[]" or "SomeClass[]". Use mixed[] for generic arrays. Correct array depth must be specified.',
                $php74 ? '' : '030 Property $prop5 not initialized by __construct(), add null doc type or set a default value',
                '033 Missing "array" type in property $prop6 type hint',
                '039 Remove property name $prop8 from @var tag',
                '045 Replace array type with typed array type in PHPDoc for property $prop10, .e.g.: "string[]" or "SomeClass[]". Use mixed[] for generic arrays. Correct array depth must be specified.',
                '045 Remove redundant property $prop10 type hints "array"',
                '051 Replace array type with typed array type in PHPDoc for C6 constant, .e.g.: "string[]" or "SomeClass[]". Use mixed[] for generic arrays. Correct array depth must be specified.',
                '051 Remove redundant C6 constant type hints "array"',
                '054 Use a more specific type in typed array hint "array[]" for C7 constant. Correct array depth must be specified.',
                '057 Use a more specific type in typed array hint "array[][]" for property $prop11. Correct array depth must be specified.',
            ],
        ];

        // #1
        $dataSets[] = [
            [
                'FqcnMethodSniff.invalidTags' => ['@SmartTemplate'],
            ],
            __DIR__ . '/fixtures/TestClass1.php',
            [
                '007 Add type declaration for parameter $a or create PHPDoc with type hint',
                '007 Create PHPDoc with typed array type hint for parameter $b, .e.g.: "string[]" or "SomeClass[]". Correct array depth must be specified.',
                '012 Add type hint in PHPDoc tag for parameter $c',
                '014 Add type declaration for return value or create PHPDoc with type hint',
                '014 Missing PHPDoc tag or void type declaration for return value',
                '019 Add type hint in PHPDoc tag for parameter $d',
                '020 Add type hint in PHPDoc tag for parameter $e, e.g. "int"',
                '021 Add type hint in PHPDoc tag for parameter $f, e.g. "SomeClass[]"',
                '024 Replace array type with typed array type in PHPDoc for return value, .e.g.: "string[]" or "SomeClass[]". Use mixed[] for generic arrays. Correct array depth must be specified.',
                '031 Change type hint for parameter $h to compound, e.g. SomeClass|null',
                '035 Add type declaration for parameter $h, e.g.: "?SomeClass"',
                '035 Add type declaration for parameter $i, e.g.: "?int"',
                '035 Add type declaration for return value, e.g.: "?array"',
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
                '087 Add @var tag for property $prop1',
                $php74 ? '' : '087 Property $prop1 not initialized by __construct(), add null doc type or set a default value',
            ],
        ];

        // #2
        $dataSets[] = [
            [
                'FqcnMethodSniff.enabled' => 'false',
            ],
            __DIR__ . '/fixtures/TestClass1.php',
            [
                '087 Add @var tag for property $prop1',
                $php74 ? '' : '087 Property $prop1 not initialized by __construct(), add null doc type or set a default value',
            ],
        ];

        // #3
        $dataSets[] = [
            [
                'useReflection' => true,
            ],
            __DIR__ . '/fixtures/TestClass3.php',
            [
                '007 Add type declaration for parameter $arg1 or create PHPDoc with type hint',
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
                'useReflection' => false,
            ],
            __DIR__ . '/fixtures/TestClass4.php',
            [
                '008 Replace array type with typed array type in PHPDoc for parameter $arg1, .e.g.: "string[]" or "SomeClass[]". Use mixed[] for generic arrays. Correct array depth must be specified.',
                '037 Type hint "static" is not compatible with return value value type',
                '037 Missing "self" type in return value type hint',
                '102 Remove @return void tag, not necessary',
                '111 Useless PHPDoc',
                '117 Use a more specific type in typed array hint "array[]" for parameter $arg2. Correct array depth must be specified.',
                '124 Type hint "mixed" is not compatible with parameter $arg1 value type',
                '126 Type hint "mixed" is not compatible with return value value type',
            ],
        ];

        // #5
        $dataSets[] = [
            [
                'useReflection' => false,
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
                '042 Add type declaration for parameter $arg1, e.g.: "float"',
                '045 Add type declaration for parameter $arg4, e.g.: "float"',
                '054 Change parameter $arg1 type declaration to nullable, e.g. ?string. Remove default null value if this argument is required.',
                '070 Change type hint for parameter $arg1 to compound, e.g. SomeClass|null',
                '074 Add type declaration for parameter $arg1, e.g.: "?SomeClass"',
                '078 Create PHPDoc with typed array type hint for parameter $arg1, .e.g.: "string[]|null" or "SomeClass[]|null". Correct array depth must be specified.',
                '083 Useless PHPDoc',
                '086 Useless PHPDoc',
                '092 Use void return value type declaration or change type to compound, e.g. SomeClass|null',
                '094 Add type declaration for return value, e.g.: "?SomeClass"',
                '101 Useless PHPDoc',
                '107 Use a more specific type in typed array hint "[]" for parameter $arg1. Correct array depth must be specified.',
                '118 Replace array type with typed array type in PHPDoc for C4 constant, .e.g.: "string[]" or "SomeClass[]". Use mixed[] for generic arrays. Correct array depth must be specified.',
                '121 Use a more specific type in typed array hint "[]" for C5 constant. Correct array depth must be specified.',
                '124 Use a more specific type in typed array hint "[][]" for C6 constant. Correct array depth must be specified.',
                '124 Type hint "null" is not compatible with C6 constant value type',
                '127 Use a more specific type in typed array hint "[][]" for property $prop2. Correct array depth must be specified.',
            ],
        ];

        // #6
        $dataSets[] = [
            [
                'useReflection'                     => false,
                'FqcnMethodSniff.reportMissingTags' => false,
            ],
            __DIR__ . '/fixtures/TestClass6.php',
            [
                '064 Add type declaration for parameter $arg1 or create PHPDoc with type hint',
                '064 Add typed array type hint for parameter $arg2, .e.g.: "string[]" or "SomeClass[]". Correct array depth must be specified.',
                '064 Add type declaration for return value or create PHPDoc with type hint',
                '072 Returned property $prop1 is nullable, add null return doc type, e.g. null|string',
                '074 Returned property $prop1 is nullable, use nullable return type declaration, e.g. ?string',
                '088 Add @var tag for property $prop5',
                $php74 ? '' : '088 Property $prop5 not initialized by __construct(), add null doc type or set a default value',
                $php74 ? '' : '095 Property $prop6 not initialized by __construct(), add null doc type or set a default value',
            ],
        ];

        // #7
        $dataSets[] = [
            [
                'useReflection' => false,
            ],
            __DIR__ . '/fixtures/TestTrait7.php',
            [
                '007 Add @var tag for property $prop1',
                $php74 ? '' : '007 Property $prop1 not initialized by __construct(), add null doc type or set a default value',
            ],
        ];

        // #8
        $dataSets[] = [
            [
                'useReflection' => false,
            ],
            __DIR__ . '/fixtures/TestInterface8.php',
            [
                '015 Add type declaration for return value or create PHPDoc with type hint',
            ],
        ];

        // #9
        $dataSets[] = [
            [
                'useReflection' => false,
            ],
            __DIR__ . '/fixtures/TestClass7.php',
            [
                $php74 ? '' : '007 Property $prop1 not initialized by __construct(), add null doc type or set a default value',
            ],
        ];

        // #10
        $dataSets[] = [
            [
                'useReflection' => false,
            ],
            __DIR__ . '/fixtures/TestClass8.php',
            [
                '020 Add type hint in PHPDoc tag for return value, e.g. "null|string"',
                '020 Returned property $foo is nullable, add null return doc type, e.g. null|string',
            ],
        ];

        // #11
        $dataSets[] = [
            [
                'useReflection'                     => false,
                'FqcnMethodSniff.reportMissingTags' => false,
            ],
            __DIR__ . '/fixtures/TestClass8.php',
            [],
        ];

        // #12
        $dataSets[] = [
            [
                'useReflection' => false,
                'addViolationId' => true
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
                '042 Add type declaration for parameter $arg1, e.g.: "float" [cbfad85f968a3e4e]',
                '045 Add type declaration for parameter $arg4, e.g.: "float" [7d078bc10c14c74e]',
                '054 Change parameter $arg1 type declaration to nullable, e.g. ?string. Remove default null value if this argument is required. [b0d14463d8e6717f]',
                '070 Change type hint for parameter $arg1 to compound, e.g. SomeClass|null [8b963bf03278b20f]',
                '074 Add type declaration for parameter $arg1, e.g.: "?SomeClass" [32fc8bc2333f3c4d]',
                '078 Create PHPDoc with typed array type hint for parameter $arg1, .e.g.: "string[]|null" or "SomeClass[]|null". Correct array depth must be specified. [6e7fdf0bab1792de]',
                '083 Useless PHPDoc [81dfff54c0c60d71]',
                '086 Useless PHPDoc [b3d61e873dc52dd3]',
                '092 Use void return value type declaration or change type to compound, e.g. SomeClass|null [40ca7ff6ecc94007]',
                '094 Add type declaration for return value, e.g.: "?SomeClass" [4969b01dfdbda587]',
                '101 Useless PHPDoc [390da7987aaad158]',
                '107 Use a more specific type in typed array hint "[]" for parameter $arg1. Correct array depth must be specified. [32f13f1f2c0119d7]',
                '118 Replace array type with typed array type in PHPDoc for C4 constant, .e.g.: "string[]" or "SomeClass[]". Use mixed[] for generic arrays. Correct array depth must be specified. [629a111a8a1d4ead]',
                '121 Use a more specific type in typed array hint "[]" for C5 constant. Correct array depth must be specified. [2e6b8365a803aeed]',
                '124 Use a more specific type in typed array hint "[][]" for C6 constant. Correct array depth must be specified. [8a453fe54b051f80]',
                '124 Type hint "null" is not compatible with C6 constant value type [79c7cfa740ac5627]',
                '127 Use a more specific type in typed array hint "[][]" for property $prop2. Correct array depth must be specified. [2987854751394f1d]',
            ],
        ];

        // #13
        $dataSets[] = [
            [
                'useReflection'                     => false,
            ],
            __DIR__ . '/fixtures/TestClass9.php',
            [
                '006 Useless description',
                '008 Useless tag',
                '025 Type hint "string" is not compatible with CONST3 constant value type',
                '025 Missing "array" type in CONST3 constant type hint',
                '031 Type hint "null" is not compatible with CONST4 constant value type',
                '043 Property $prop2 not initialized by __construct(), add null doc type or set a default value',
                '053 Add type declaration for return value, e.g.: "array"',
                '060 Missing "null" type in parameter $b type hint',
                '070 Add type declaration for parameter $d, e.g.: "array"',
                '071 Add type declaration for return value, e.g.: "array"',
                '075 Property $prop4 not initialized by __construct(), add null doc type or set a default value',
                '078 Property $prop5 not initialized by __construct(), add null doc type or set a default value',
                '082 Type hint "?string" is not compatible with parameter $a value type',
                '082 Missing "null, string" types in parameter $a type hint',
                '083 Type hint "?int" is not compatible with return value value type',
                '083 Missing "null, int" types in return value type hint',
            ],
        ];

        // Remove empty warnings (some warnings disabled on PHP 7.4)
        foreach ($dataSets as &$dataSet) {
            $dataSet[2] = array_values(array_filter($dataSet[2]));
        }

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
        array $expectedWarnings
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
            foreach ($colWarnings as $column => $warnings) {
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
