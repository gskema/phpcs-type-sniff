<?php

namespace Gskema\TypeSniff\Core\CodeElement;

use Gskema\TypeSniff\Core\CodeElement\Element\InterfaceConstElement;
use Gskema\TypeSniff\Core\CodeElement\Element\InterfaceElement;
use Gskema\TypeSniff\Core\CodeElement\Element\InterfaceMethodElement;
use Gskema\TypeSniff\Core\CodeElement\Element\TraitElement;
use Gskema\TypeSniff\Core\CodeElement\Element\TraitMethodElement;
use Gskema\TypeSniff\Core\CodeElement\Element\TraitPropElement;
use Gskema\TypeSniff\Core\Type\Common\BoolType;
use Gskema\TypeSniff\Core\Type\Common\FloatType;
use Gskema\TypeSniff\Core\Type\Common\VoidType;
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
            'givenUseReflection' => false,
            'givenFile' => __DIR__.'/fixtures/TestClass0.php.txt',
            'expectedElements' => [
                new FileElement(
                    1,
                    new DocBlock([3 => 'File Doc Comment'], []),
                    __DIR__.'/fixtures/TestClass0.php.txt'
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
                    'CONST1',
                    new IntType()
                ),
                new ClassConstElement(
                    18,
                    new DocBlock([], [
                        new VarTag(17, new IntType(), null, null),
                    ]),
                    'Test\\Name\\Space\\TestClass',
                    'CONST2',
                    new IntType()
                ),
                new ClassPropElement(
                    20,
                    new UndefinedDocBlock(),
                    'Test\\Name\\Space\\TestClass',
                    'prop1',
                    null
                ),
                new ClassPropElement(
                    25,
                    new DocBlock([], [
                        new VarTag(23, new IntType(), null, null)
                    ]),
                    'Test\\Name\\Space\\TestClass',
                    'prop2',
                    null
                ),
                new ClassPropElement(
                    28,
                    new DocBlock([], [
                        new VarTag(27, new CompoundType([new StringType(), new NullType()]), null, null)
                    ]),
                    'Test\\Name\\Space\\TestClass',
                    'prop3',
                    null
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
            'givenUseReflection' => false,
            'givenFile' => __DIR__.'/fixtures/TestClass1.php.txt',
            'expectedElements' => [
                new FileElement(
                    1,
                    new UndefinedDocBlock(),
                    __DIR__.'/fixtures/TestClass1.php.txt'
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
                    'CONST1',
                    new IntType()
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
                    'prop1',
                    null
                ),
                new ClassConstElement(
                    59,
                    new UndefinedDocBlock(),
                    'Gskema\TypeSniff\\Core\\CodeElement\\TestClass2',
                    'CONST2',
                    new IntType()
                ),
            ],
        ];

        // #2
        $dataSets[] = [
            'givenUseReflection' => false,
            'givenFile' => __DIR__.'/fixtures/TestClass2.php.txt',
            'expectedElements' => [
                new FileElement(
                    1,
                    new UndefinedDocBlock(),
                    __DIR__.'/fixtures/TestClass2.php.txt'
                ),
                new ClassElement(
                    3,
                    new UndefinedDocBlock(),
                    'TestClass3'
                ),
                new ClassConstElement(
                    5,
                    new UndefinedDocBlock(),
                    'TestClass3',
                    'C01',
                    new NullType()
                ),
                new ClassConstElement(
                    6,
                    new UndefinedDocBlock(),
                    'TestClass3',
                    'C02',
                    new BoolType()
                ),
                new ClassConstElement(
                    7,
                    new UndefinedDocBlock(),
                    'TestClass3',
                    'C03',
                    new BoolType()
                ),
                new ClassConstElement(
                    8,
                    new UndefinedDocBlock(),
                    'TestClass3',
                    'C04',
                    new IntType()
                ),
                new ClassConstElement(
                    9,
                    new UndefinedDocBlock(),
                    'TestClass3',
                    'C05',
                    new FloatType()
                ),
                new ClassConstElement(
                    10,
                    new UndefinedDocBlock(),
                    'TestClass3',
                    'C06',
                    new IntType()
                ),
                new ClassConstElement(
                    11,
                    new UndefinedDocBlock(),
                    'TestClass3',
                    'C07',
                    new StringType()
                ),
                new ClassConstElement(
                    12,
                    new UndefinedDocBlock(),
                    'TestClass3',
                    'C08',
                    new StringType()
                ),
                new ClassConstElement(
                    13,
                    new UndefinedDocBlock(),
                    'TestClass3',
                    'C09',
                    new StringType()
                ),
                new ClassConstElement(
                    16,
                    new UndefinedDocBlock(),
                    'TestClass3',
                    'C10',
                    new ArrayType()
                ),
                new ClassConstElement(
                    17,
                    new UndefinedDocBlock(),
                    'TestClass3',
                    'C11',
                    new ArrayType()
                ),
                new ClassConstElement(
                    18,
                    new UndefinedDocBlock(),
                    'TestClass3',
                    'C12',
                    null
                ),
                new ClassConstElement(
                    19,
                    new UndefinedDocBlock(),
                    'TestClass3',
                    'C13',
                    null
                ),
                new ClassConstElement(
                    20,
                    new UndefinedDocBlock(),
                    'TestClass3',
                    'C14',
                    new IntType()
                ),
                new ClassPropElement(
                    21,
                    new UndefinedDocBlock(),
                    'TestClass3',
                    'prop1',
                    null
                ),
                new ClassPropElement(
                    22,
                    new UndefinedDocBlock(),
                    'TestClass3',
                    'prop2',
                    new IntType()
                ),
            ]
        ];

        // #3
        $dataSets[] = [
            'givenUseReflection' => false,
            'givenFile' => __DIR__.'/fixtures/TestInterface0.php.txt',
            'expectedElements' => [
                new FileElement(1, new UndefinedDocBlock(), __DIR__.'/fixtures/TestInterface0.php.txt'),
                new InterfaceElement(3, new UndefinedDocBlock(), 'TestInterface0'),
                new InterfaceConstElement(5, new UndefinedDocBlock(), 'TestInterface0', 'C1', new IntType()),
                new InterfaceMethodElement(
                    new UndefinedDocBlock(),
                    'TestInterface0',
                    new FunctionSignature(7, 'func1', [], new VoidType(), 7),
                    null
                ),
            ]
        ];

        // #4
        $dataSets[] = [
            'givenUseReflection' => false,
            'givenFile' => __DIR__.'/fixtures/TestTrait0.php.txt',
            'expectedElements' => [
                new FileElement(1, new UndefinedDocBlock(), __DIR__.'/fixtures/TestTrait0.php.txt'),
                new TraitElement(3, new UndefinedDocBlock(), 'TestTrait0'),
                new TraitPropElement(5, new UndefinedDocBlock(), 'TestTrait0', 'prop1', new IntType()),
                new TraitMethodElement(
                    new UndefinedDocBlock(),
                    'TestTrait0',
                    new FunctionSignature(
                        7,
                        'func1',
                        [
                            new FunctionParam(7, 'arg1', new IntType())
                        ],
                        new IntType(),
                        7
                    ),
                    null
                ),
            ]
        ];

        // #5
        $dataSets[] = [
            'givenUseReflection' => true,
            'givenFile' => __DIR__.'/fixtures/Ref2.php',
            'expectedElements' => [
                new FileElement(1, new UndefinedDocBlock(), __DIR__.'/fixtures/Ref2.php'),
                new ClassElement(5, new UndefinedDocBlock(), 'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\Ref2'),
                new ClassMethodElement(
                    new UndefinedDocBlock(),
                    'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\Ref2',
                    new FunctionSignature(
                        7,
                        'func0',
                        [],
                        new VoidType(),
                        7
                    ),
                    true
                ),
                new ClassMethodElement(
                    new UndefinedDocBlock(),
                    'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\Ref2',
                    new FunctionSignature(
                        11,
                        'func1',
                        [],
                        new VoidType(),
                        12
                    ),
                    true
                ),
                new ClassMethodElement(
                    new UndefinedDocBlock(),
                    'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\Ref2',
                    new FunctionSignature(
                        15,
                        'func2',
                        [],
                        new UndefinedType(),
                        15
                    ),
                    false
                ),
            ]
        ];

        // #6
        $dataSets[] = [
            'givenUseReflection' => false,
            'givenFile' => __DIR__.'/fixtures/Parse0.php',
            'expectedElements' => [
                new FileElement(1, new UndefinedDocBlock(), __DIR__.'/fixtures/Parse0.php'),
                new ClassElement(5, new UndefinedDocBlock(), 'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\Parse0'),
                new ClassMethodElement(
                    new UndefinedDocBlock(),
                    'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\Parse0',
                    new FunctionSignature(
                        7,
                        'func1',
                        [],
                        new VoidType(),
                        7
                    ),
                    null
                ),
                new ClassPropElement(
                    11,
                    new UndefinedDocBlock(),
                    'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\Parse0',
                    'prop1',
                    null // failed to parse
                )
            ]
        ];

        // #7
        $dataSets[] = [
            'givenUseReflection' => true,
            'givenFile' => __DIR__.'/fixtures/Parse1.php',
            'expectedElements' => [
                new FileElement(1, new UndefinedDocBlock(), __DIR__.'/fixtures/Parse1.php'),
                new ClassElement(5, new UndefinedDocBlock(), 'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\Parse1'),
                new ClassMethodElement(
                    new UndefinedDocBlock(),
                    'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\Parse1',
                    new FunctionSignature(
                        7,
                        'func1',
                        [],
                        new VoidType(),
                        7
                    ),
                    false // ParseError
                ),
            ]
        ];

        return $dataSets;
    }

    /**
     * @dataProvider dataDetectFromTokens
     *
     * @param bool                   $givenUseReflection
     * @param string                 $givenPath
     * @param CodeElementInterface[] $expected
     */
    public function testDetectFromTokens(bool $givenUseReflection, string $givenPath, array $expected): void
    {
        $givenFile = new LocalFile($givenPath, new Ruleset(new Config()), new Config());
        $givenFile->parse();

        $actual = CodeElementDetector::detectFromTokens($givenFile, $givenUseReflection);

        self::assertEquals($expected, $actual);
    }
}
