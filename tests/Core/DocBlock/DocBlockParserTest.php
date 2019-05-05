<?php

namespace Gskema\TypeSniff\Core\DocBlock;

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\LocalFile;
use PHP_CodeSniffer\Ruleset;
use PHPUnit\Framework\TestCase;
use Gskema\TypeSniff\Core\DocBlock\Tag\GenericTag;
use Gskema\TypeSniff\Core\DocBlock\Tag\ParamTag;
use Gskema\TypeSniff\Core\DocBlock\Tag\ReturnTag;
use Gskema\TypeSniff\Core\DocBlock\Tag\VarTag;
use Gskema\TypeSniff\Core\Type\Common\ArrayType;
use Gskema\TypeSniff\Core\Type\Common\IntType;
use Gskema\TypeSniff\Core\Type\Common\StringType;
use Gskema\TypeSniff\Core\Type\DocBlock\CompoundType;
use Gskema\TypeSniff\Core\Type\DocBlock\TypedArrayType;

class DocBlockParserTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public function dataDetectFromTokens(): array
    {
        $dataSets = [];

        // #0
        $dataSets[] = [
            __DIR__.'/fixtures/TestDocBlock.php.huh',
            [2, 55],
            new DocBlock(
                [
                    4 => 'FuncDesc',
                    5 => 'oops',
                    6 => '',
                    7 => ' MultiLine',
                ],
                [
                    new ParamTag(10, new IntType(), 'param1', 'ParamDesc SecondLine'),
                    new GenericTag(12, 'inheritdoc', null),
                    new ReturnTag(14, new ArrayType(), null)
                ]
            )
        ];

        // #1
        $dataSets[] = [
            __DIR__.'/fixtures/TestDocBlock.php.huh',
            [58, 63],
            new DocBlock(
                [],
                [
                    new VarTag(17, new IntType(), null, 'SomeDesc'),
                ]
            )
        ];

        // #2
        $dataSets[] = [
            __DIR__.'/fixtures/TestDocBlock.php.huh',
            [66, 71],
            new DocBlock(
                [],
                [
                    new VarTag(
                        19,
                        new TypedArrayType(new StringType(), 1),
                        'inlineVar',
                        'Desc1'
                    ),
                ]
            )
        ];

        // #3
        $dataSets[] = [
            __DIR__.'/fixtures/TestDocBlock.php.huh',
            [75, 158],
            new DocBlock(
                [
                    23 => 'FuncDesc',
                    24 => 'oops wtf',
                    25 => '',
                    26 => 'array(',
                    27 => '  example',
                    28 => ')',
                    29 => ' MultiLine MultiLine',
                ],
                [
                    new ParamTag(32, new IntType(), 'param1', 'ParamDesc SecondLine'),
                    new ParamTag(
                        34,
                        new CompoundType([new TypedArrayType(new StringType(), 1), new IntType()]),
                        'param2',
                        null
                    ),
                    new GenericTag(35, 'deprecated', null),
                    new GenericTag(36, 'inheritdoc', null),
                    new ReturnTag(38, new TypedArrayType(new StringType(), 1), null)
                ]
            )
        ];

        return $dataSets;
    }

    /**
     * @dataProvider dataDetectFromTokens
     *
     * @param string   $givenPath
     * @param int[]    $givenPointers
     * @param DocBlock $expected
     */
    public function testDetectFromTokens(
        string $givenPath,
        array $givenPointers,
        DocBlock $expected
    ): void {
        $givenFile = new LocalFile($givenPath, new Ruleset(new Config()), new Config());
        $givenFile->parse();

        $actual = DocBlockParser::fromTokens($givenFile, $givenPointers[0], $givenPointers[1]);

        self::assertEquals($expected, $actual);
    }
}
