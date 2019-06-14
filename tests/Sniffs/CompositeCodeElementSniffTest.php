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
            [],
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
