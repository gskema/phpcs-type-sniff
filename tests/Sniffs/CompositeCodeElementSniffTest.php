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
                'FqcnMethodSniff.enabled' => 'true',
            ],
            __DIR__.'/fixtures/Class0.php.txt',
            [
                '010 Replace array type with typed array type in PHPDoc for C2 constant. Use mixed[] for generic arrays.',
                '012 Add PHPDoc with typed array type hint for C3 constant. Use mixed[] for generic arrays.',
                '017 Add PHPDoc for property $prop1',
                '022 Add @var tag for property $prop2',
                '025 Add type hint to @var tag for property $prop3',
                '028 Replace array type with typed array type in PHPDoc for property $prop4. Use mixed[] for generic arrays.',
                '031 Replace array type with typed array type in PHPDoc for property $prop5. Use mixed[] for generic arrays.',
                '034 Add PHPDoc with typed array type hint for property $prop6. Use mixed[] for generic arrays.',
                '040 Remove property name $prop8 from @var tag',
            ]
        ];

        // #1
        $dataSets[] = [
            [
                'FqcnMethodSniff.usefulTags' => ['@SmartTemplate'],
            ],
            __DIR__.'/fixtures/Class1.php.txt',
            [
                '007 Add type declaration for parameter $a or create PHPDoc with type hint',
                '007 Create PHPDoc with typed array type hint for parameter $b, .e.g.: "string[]" or "SomeClass[]"',
                '012 Add type hint in PHPDoc tag for parameter $c',
                '014 Missing PHPDoc tag or void type declaration for return value',
                '019 Add type hint in PHPDoc tag for parameter $d',
                '020 Add type hint in PHPDoc tag for parameter $e, e.g. "int"',
                '021 Add type hint in PHPDoc tag for parameter $f',
                '024 Replace array type with typed array type in PHPDoc for return value. Use mixed[] for generic arrays.',
                '035 Add type declaration for parameter $h',
                '035 Add type declaration for parameter $i, e.g.: "?int"',
                '035 Add type declaration for return value, e.g.: "?array"',
                '040 Remove array type, typed array type is present in PHPDoc for parameter $j.',
                '041 Replace array type with typed array type in PHPDoc for parameter $k. Use mixed[] for generic arrays.',
                '042 Add type hint in PHPDoc tag for parameter $l',
                '044 Add "null" type hint in PHPDoc for parameter $n',
                '045 Add "int" type hint in PHPDoc for parameter $o',
                '046 Add "null" type hint in PHPDoc for parameter $p',
                '065 Useless PHPDoc',
                '087 Add PHPDoc for property $prop1',
            ]
        ];

        // #2
        $dataSets[] = [
            [
                'FqcnMethodSniff.enabled' => 'false',
            ],
            __DIR__.'/fixtures/Class1.php.txt',
            [
                '087 Add PHPDoc for property $prop1',
            ]
        ];

        // #3
        $dataSets[] = [
            [],
            __DIR__.'/fixtures/Class3.php',
            [
                '007 Add type declaration for parameter $arg1 or create PHPDoc with type hint',
                '027 Add "null" type hint in PHPDoc for parameter $arg1',
                '042 Add "null" type hint in PHPDoc for return value',
            ]
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
        array $expectedWarnings
    ): void {
        static::assertFileExists($givenPath);

        $givenFile = new LocalFile($givenPath, new Ruleset(new Config()), new Config());
        $givenFile->parse();

        $ref = './src/Sniffs/CompositeCodeElementSniff.php'; // @see phpcs.xml
        $givenFile->ruleset->ruleset[$ref] = [
            'properties' => $givenConfig
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
                    $actualWarnings[] = $lineKey.' '.$warning['message'];
                }
            }
        }

        static::assertEquals($expectedWarnings, $actualWarnings);
    }
}
