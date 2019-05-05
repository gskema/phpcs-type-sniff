<?php

namespace Gskema\TypeSniff\Core\CodeElement;

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\LocalFile;
use PHP_CodeSniffer\Ruleset;
use PHPUnit\Framework\TestCase;
use Gskema\TypeSniff\Core\CodeElement\Element\ClassConstElement;
use Gskema\TypeSniff\Core\CodeElement\Element\ClassElement;
use Gskema\TypeSniff\Core\CodeElement\Element\ClassMethodElement;
use Gskema\TypeSniff\Core\CodeElement\Element\ClassPropElement;
use Gskema\TypeSniff\Core\CodeElement\Element\CodeElementInterface;
use Gskema\TypeSniff\Core\CodeElement\Element\ConstElement;
use Gskema\TypeSniff\Core\CodeElement\Element\FileElement;
use Gskema\TypeSniff\Core\CodeElement\Element\FunctionElement;
use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\DocBlock\Tag\GenericTag;
use Gskema\TypeSniff\Core\DocBlock\Tag\ParamTag;
use Gskema\TypeSniff\Core\DocBlock\Tag\ReturnTag;
use Gskema\TypeSniff\Core\DocBlock\Tag\VarTag;
use Gskema\TypeSniff\Core\DocBlock\UndefinedDocBlock;
use Gskema\TypeSniff\Core\Func\FunctionParam;
use Gskema\TypeSniff\Core\Func\FunctionSignature;
use Gskema\TypeSniff\Core\Type\Common\ArrayType;
use Gskema\TypeSniff\Core\Type\Common\IntType;
use Gskema\TypeSniff\Core\Type\Common\StringType;
use Gskema\TypeSniff\Core\Type\Common\UndefinedType;
use Gskema\TypeSniff\Core\Type\Declaration\NullableType;
use Gskema\TypeSniff\Core\Type\DocBlock\CompoundType;
use Gskema\TypeSniff\Core\Type\DocBlock\NullType;

class CodeElementDetectorTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public function dataDetectFromTokens(): array
    {
        $dataSets = [];

        // #0
        $dataSets[] = [
            __DIR__.'/fixtures/TestClass.php.huh',
            [
                new FileElement(
                    1,
                    new DocBlock([3 => 'File Doc Comment'], []),
                    __DIR__.'/fixtures/TestClass.php.huh'
                ),
                new ClassElement(
                    13,
                    new DocBlock([9 => 'Class TestClass'], [
                        new GenericTag(11, 'package', 'Gskema\TypeSniff\Core\DocBlock\fixtures')
                    ]),
                    'Test\\Name\\Space\\TestClass'
                ),
                new ClassConstElement(
                    15,
                    new UndefinedDocBlock(),
                    'Test\\Name\\Space\\TestClass',
                    'CONST1'
                ),
                new ClassConstElement(
                    18,
                    new DocBlock([], [
                        new VarTag(17, new IntType(), null, null),
                    ]),
                    'Test\\Name\\Space\\TestClass',
                    'CONST2'
                ),
                new ClassPropElement(
                    20,
                    new UndefinedDocBlock(),
                    'Test\\Name\\Space\\TestClass',
                    'prop1'
                ),
                new ClassPropElement(
                    25,
                    new DocBlock([], [
                        new VarTag(23, new IntType(), null, null)
                    ]),
                    'Test\\Name\\Space\\TestClass',
                    'prop2'
                ),
                new ClassPropElement(
                    28,
                    new DocBlock([], [
                        new VarTag(27, new CompoundType([new StringType(), new NullType()]), null, null)
                    ]),
                    'Test\\Name\\Space\\TestClass',
                    'prop3'
                ),
                new ClassMethodElement(
                    new UndefinedDocBlock(),
                    'Test\\Name\\Space\\TestClass',
                    new FunctionSignature(
                        30,
                        '__construct',
                        [],
                        new UndefinedType(),
                        30
                    ),
                    null
                ),
                new ClassMethodElement(
                    new UndefinedDocBlock(),
                    'Test\\Name\\Space\\TestClass',
                    new FunctionSignature(34, 'method1', [], new StringType(), 34),
                    null
                ),
                new ClassMethodElement(
                    new DocBlock([], [
                        new ParamTag(40, new IntType(), 'param1', null),
                        new ReturnTag(42, new CompoundType([new ArrayType(), new NullType()]), null)
                    ]),
                    'Test\\Name\\Space\\TestClass',
                    new FunctionSignature(
                        44,
                        'method2',
                        [
                            new FunctionParam(44, 'param1', new IntType())
                        ],
                        new NullableType(new ArrayType()),
                        44
                    ),
                    null
                ),
            ]
        ];

        // #1
        $dataSets[] = [
            __DIR__.'/fixtures/TestClass2.php.huh',
            [
                new FileElement(
                    1,
                    new UndefinedDocBlock(),
                    __DIR__.'/fixtures/TestClass2.php.huh'
                ),
                new FunctionElement(
                    7,
                    new UndefinedDocBlock(),
                    'Gskema\TypeSniff\\Core\\CodeElement',
                    new FunctionSignature(7, 'namedFunc', [], new UndefinedType(), 7)
                ),
                new ConstElement(
                    11,
                    new UndefinedDocBlock(),
                    'Gskema\TypeSniff\\Core\\CodeElement',
                    'CONST1'
                ),
                new ClassElement(
                    14,
                    new UndefinedDocBlock(),
                    'Gskema\TypeSniff\\Core\\CodeElement\\TestClass2'
                ),
                new ClassMethodElement(
                    new UndefinedDocBlock(),
                    'Gskema\TypeSniff\\Core\\CodeElement\\TestClass2',
                    new FunctionSignature(16, 'method1', [], new UndefinedType(), 16),
                    null
                ),
                new ClassPropElement(
                    57,
                    new DocBlock([], [
                        new VarTag(56, new IntType(), null, null),
                    ]),
                    'Gskema\TypeSniff\\Core\\CodeElement\\TestClass2',
                    'prop1'
                ),
                new ClassConstElement(
                    59,
                    new UndefinedDocBlock(),
                    'Gskema\TypeSniff\\Core\\CodeElement\\TestClass2',
                    'CONST2'
                ),
            ],
        ];

        return $dataSets;
    }

    /**
     * @dataProvider dataDetectFromTokens
     *
     * @param string        $givenPath
     * @param CodeElementInterface[] $expected
     */
    public function testDetectFromTokens(string $givenPath, array $expected): void
    {
        $givenFile = new LocalFile($givenPath, new Ruleset(new Config()), new Config());
        $givenFile->parse();

        $actual = CodeElementDetector::detectFromTokens($givenFile, false);

        self::assertEquals($expected, $actual);
    }
}
