<?php

namespace Gskema\TypeSniff\Core\CodeElement;

use Gskema\TypeSniff\Core\CodeElement\Element\InterfaceConstElement;
use Gskema\TypeSniff\Core\CodeElement\Element\InterfaceElement;
use Gskema\TypeSniff\Core\CodeElement\Element\InterfaceMethodElement;
use Gskema\TypeSniff\Core\CodeElement\Element\TraitElement;
use Gskema\TypeSniff\Core\CodeElement\Element\TraitMethodElement;
use Gskema\TypeSniff\Core\CodeElement\Element\TraitPropElement;
use Gskema\TypeSniff\Core\Type\Common\BoolType;
use Gskema\TypeSniff\Core\Type\Common\CallableType;
use Gskema\TypeSniff\Core\Type\Common\FloatType;
use Gskema\TypeSniff\Core\Type\Common\IterableType;
use Gskema\TypeSniff\Core\Type\Common\ObjectType;
use Gskema\TypeSniff\Core\Type\Common\ParentType;
use Gskema\TypeSniff\Core\Type\Common\ResourceType;
use Gskema\TypeSniff\Core\Type\Common\SelfType;
use Gskema\TypeSniff\Core\Type\Common\VoidType;
use Gskema\TypeSniff\Core\Type\DocBlock\DoubleType;
use Gskema\TypeSniff\Core\Type\DocBlock\FalseType;
use Gskema\TypeSniff\Core\Type\DocBlock\MixedType;
use Gskema\TypeSniff\Core\Type\DocBlock\StaticType;
use Gskema\TypeSniff\Core\Type\DocBlock\ThisType;
use Gskema\TypeSniff\Core\Type\DocBlock\TrueType;
use Gskema\TypeSniff\Core\Type\DocBlock\TypedArrayType;
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
            'givenFile' => __DIR__.'/fixtures/TestClass0.php',
            'expectedElements' => [
                new FileElement(
                    1,
                    new DocBlock([3 => 'File Doc Comment'], []),
                    __DIR__.'/fixtures/TestClass0.php'
                ),
                new ClassElement(
                    13,
                    new DocBlock([9 => 'Class TestClass0'], [
                        new GenericTag(11, 'package', 'Gskema\TypeSniff\Core\DocBlock\fixtures')
                    ]),
                    'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestClass0'
                ),
                new ClassConstElement(
                    15,
                    new UndefinedDocBlock(),
                    'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestClass0',
                    'CONST1',
                    new IntType()
                ),
                new ClassConstElement(
                    18,
                    new DocBlock([], [
                        new VarTag(17, new IntType(), null, null),
                    ]),
                    'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestClass0',
                    'CONST2',
                    new IntType()
                ),
                new ClassPropElement(
                    20,
                    new UndefinedDocBlock(),
                    'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestClass0',
                    'prop1',
                    null
                ),
                new ClassPropElement(
                    25,
                    new DocBlock([], [
                        new VarTag(23, new IntType(), null, null)
                    ]),
                    'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestClass0',
                    'prop2',
                    null
                ),
                new ClassPropElement(
                    28,
                    new DocBlock([], [
                        new VarTag(27, new CompoundType([new StringType(), new NullType()]), null, null)
                    ]),
                    'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestClass0',
                    'prop3',
                    null
                ),
                new ClassMethodElement(
                    new UndefinedDocBlock(),
                    'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestClass0',
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
                    'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestClass0',
                    new FunctionSignature(34, 'method1', [], new StringType(), 34),
                    null
                ),
                new ClassMethodElement(
                    new DocBlock([], [
                        new ParamTag(40, new IntType(), 'param1', null),
                        new ReturnTag(42, new CompoundType([new ArrayType(), new NullType()]), null)
                    ]),
                    'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestClass0',
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
            'givenFile' => __DIR__.'/fixtures/TestClass1.php',
            'expectedElements' => [
                new FileElement(
                    1,
                    new UndefinedDocBlock(),
                    __DIR__.'/fixtures/TestClass1.php'
                ),
                new FunctionElement(
                    7,
                    new UndefinedDocBlock(),
                    'Gskema\TypeSniff\\Core\\CodeElement\\fixtures',
                    new FunctionSignature(7, 'namedFunc', [], new UndefinedType(), 7)
                ),
                new ConstElement(
                    11,
                    new UndefinedDocBlock(),
                    'Gskema\TypeSniff\\Core\\CodeElement\\fixtures',
                    'CONST1',
                    new IntType()
                ),
                new ClassElement(
                    14,
                    new UndefinedDocBlock(),
                    'Gskema\TypeSniff\\Core\\CodeElement\\fixtures\\TestClass1'
                ),
                new ClassMethodElement(
                    new UndefinedDocBlock(),
                    'Gskema\TypeSniff\\Core\\CodeElement\\fixtures\\TestClass1',
                    new FunctionSignature(16, 'method1', [], new UndefinedType(), 16),
                    null
                ),
                new ClassPropElement(
                    57,
                    new DocBlock([], [
                        new VarTag(56, new IntType(), null, null),
                    ]),
                    'Gskema\TypeSniff\\Core\\CodeElement\\fixtures\\TestClass1',
                    'prop1',
                    null
                ),
                new ClassConstElement(
                    59,
                    new UndefinedDocBlock(),
                    'Gskema\TypeSniff\\Core\\CodeElement\\fixtures\\TestClass1',
                    'CONST2',
                    new IntType()
                ),
            ],
        ];

        // #2
        $dataSets[] = [
            'givenUseReflection' => false,
            'givenFile' => __DIR__.'/fixtures/TestClass2.php',
            'expectedElements' => [
                new FileElement(
                    1,
                    new UndefinedDocBlock(),
                    __DIR__.'/fixtures/TestClass2.php'
                ),
                new ClassElement(
                    5,
                    new UndefinedDocBlock(),
                    'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass2'
                ),
                new ClassConstElement(
                    7,
                    new UndefinedDocBlock(),
                    'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass2',
                    'C01',
                    new NullType()
                ),
                new ClassConstElement(
                    8,
                    new UndefinedDocBlock(),
                    'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass2',
                    'C02',
                    new BoolType()
                ),
                new ClassConstElement(
                    9,
                    new UndefinedDocBlock(),
                    'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass2',
                    'C03',
                    new BoolType()
                ),
                new ClassConstElement(
                    10,
                    new UndefinedDocBlock(),
                    'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass2',
                    'C04',
                    new IntType()
                ),
                new ClassConstElement(
                    11,
                    new UndefinedDocBlock(),
                    'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass2',
                    'C05',
                    new FloatType()
                ),
                new ClassConstElement(
                    12,
                    new UndefinedDocBlock(),
                    'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass2',
                    'C06',
                    new IntType()
                ),
                new ClassConstElement(
                    13,
                    new UndefinedDocBlock(),
                    'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass2',
                    'C07',
                    new StringType()
                ),
                new ClassConstElement(
                    14,
                    new UndefinedDocBlock(),
                    'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass2',
                    'C08',
                    new StringType()
                ),
                new ClassConstElement(
                    15,
                    new UndefinedDocBlock(),
                    'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass2',
                    'C09',
                    new StringType()
                ),
                new ClassConstElement(
                    18,
                    new UndefinedDocBlock(),
                    'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass2',
                    'C10',
                    new ArrayType()
                ),
                new ClassConstElement(
                    19,
                    new UndefinedDocBlock(),
                    'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass2',
                    'C11',
                    new ArrayType()
                ),
                new ClassConstElement(
                    20,
                    new UndefinedDocBlock(),
                    'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass2',
                    'C12',
                    null
                ),
                new ClassConstElement(
                    21,
                    new UndefinedDocBlock(),
                    'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass2',
                    'C13',
                    null
                ),
                new ClassConstElement(
                    22,
                    new UndefinedDocBlock(),
                    'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass2',
                    'C14',
                    new IntType()
                ),
                new ClassPropElement(
                    23,
                    new UndefinedDocBlock(),
                    'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass2',
                    'prop1',
                    null
                ),
                new ClassPropElement(
                    24,
                    new UndefinedDocBlock(),
                    'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass2',
                    'prop2',
                    new IntType()
                ),
            ]
        ];

        // #3
        $dataSets[] = [
            'givenUseReflection' => false,
            'givenFile' => __DIR__.'/fixtures/TestInterface0.php',
            'expectedElements' => [
                new FileElement(1, new UndefinedDocBlock(), __DIR__.'/fixtures/TestInterface0.php'),
                new InterfaceElement(5, new UndefinedDocBlock(), 'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestInterface0'),
                new InterfaceConstElement(7, new UndefinedDocBlock(), 'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestInterface0', 'C1', new IntType()),
                new InterfaceMethodElement(
                    new UndefinedDocBlock(),
                    'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestInterface0',
                    new FunctionSignature(9, 'func1', [], new VoidType(), 9),
                    null
                ),
            ]
        ];

        // #4
        $dataSets[] = [
            'givenUseReflection' => false,
            'givenFile' => __DIR__.'/fixtures/TestTrait0.php',
            'expectedElements' => [
                new FileElement(1, new UndefinedDocBlock(), __DIR__.'/fixtures/TestTrait0.php'),
                new TraitElement(5, new UndefinedDocBlock(), 'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestTrait0'),
                new TraitPropElement(7, new UndefinedDocBlock(), 'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestTrait0', 'prop1', new IntType()),
                new TraitMethodElement(
                    new UndefinedDocBlock(),
                    'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestTrait0',
                    new FunctionSignature(
                        9,
                        'func1',
                        [
                            new FunctionParam(9, 'arg1', new IntType())
                        ],
                        new IntType(),
                        9
                    ),
                    null
                ),
            ]
        ];

        // #5
        $dataSets[] = [
            'givenUseReflection' => true,
            'givenFile' => __DIR__.'/fixtures/TestRef2.php',
            'expectedElements' => [
                new FileElement(1, new UndefinedDocBlock(), __DIR__.'/fixtures/TestRef2.php'),
                new ClassElement(5, new UndefinedDocBlock(), 'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestRef2'),
                new ClassMethodElement(
                    new UndefinedDocBlock(),
                    'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestRef2',
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
                    'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestRef2',
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
                    'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestRef2',
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
            'givenFile' => __DIR__.'/fixtures/TestParse0.php',
            'expectedElements' => [
                new FileElement(1, new UndefinedDocBlock(), __DIR__.'/fixtures/TestParse0.php'),
                new ClassElement(5, new UndefinedDocBlock(), 'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestParse0'),
                new ClassMethodElement(
                    new UndefinedDocBlock(),
                    'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestParse0',
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
                    'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestParse0',
                    'prop1',
                    null // failed to parse
                )
            ]
        ];

        // #7
        $dataSets[] = [
            'givenUseReflection' => true,
            'givenFile' => __DIR__.'/fixtures/TestParse1.php',
            'expectedElements' => [
                new FileElement(1, new UndefinedDocBlock(), __DIR__.'/fixtures/TestParse1.php'),
                new ClassElement(5, new UndefinedDocBlock(), 'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestParse1'),
                new ClassMethodElement(
                    new UndefinedDocBlock(),
                    'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestParse1',
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

        // #8
        $dataSets[] = [
            'givenUseReflection' => false,
            'givenFile' => __DIR__.'/fixtures/TestClass3.php',
            'expectedElements' => [
                new FileElement(1, new UndefinedDocBlock(), __DIR__.'/fixtures/TestClass3.php'),
                new ClassElement(5, new UndefinedDocBlock(), 'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass3'),
                new ClassMethodElement(
                    new DocBlock([], [
                        new ReturnTag(8, new VoidType(), null)
                    ]),
                    'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestClass3',
                    new FunctionSignature(
                        10,
                        'method1',
                        [],
                        new VoidType(),
                        10
                    ),
                    null
                ),
                new ClassMethodElement(
                    new DocBlock([], [
                        new ParamTag(15, new ArrayType(), 'array', null),
                        new ParamTag(16, new BoolType(), 'bool', null),
                        new ParamTag(17, new BoolType(), 'boolean', null),
                        new ParamTag(18, new CallableType(), 'callable', null),
                        new ParamTag(19, new DoubleType(), 'double', null),
                        new ParamTag(20, new FalseType(), 'false', null),
                        new ParamTag(21, new FloatType(), 'float', null),
                        new ParamTag(22, new IntType(), 'int', null),
                        new ParamTag(23, new IntType(), 'integer', null),
                        new ParamTag(24, new IterableType(), 'iterable', null),
                        new ParamTag(25, new MixedType(), 'mixed', null),
                        new ParamTag(26, new NullType(), 'null', null),
                        new ParamTag(27, new ObjectType(), 'object', null),
                        new ParamTag(28, new ParentType(), 'parent', null),
                        new ParamTag(29, new ResourceType(), 'resource', null),
                        new ParamTag(30, new SelfType(), 'self', null),
                        new ParamTag(31, new StaticType(), 'static', null),
                        new ParamTag(32, new StringType(), 'string', null),
                        new ParamTag(33, new TrueType(), 'true', null),
                        new ParamTag(34, new UndefinedType(), 'undefined', null),
                        new ParamTag(35, new TypedArrayType(new IntType(), 1), 'typedArray', null),
                        new ParamTag(36, new CompoundType([new IntType(), new NullType()]), 'nullableInt', null),
                        new ReturnTag(37, new ThisType(), null)
                    ]),
                    'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestClass3',
                    new FunctionSignature(
                        39,
                        'method2',
                        [
                            new FunctionParam(40, 'array', new ArrayType()),
                            new FunctionParam(41, 'bool', new BoolType()),
                            new FunctionParam(42, 'boolean', new UndefinedType()),
                            new FunctionParam(43, 'callable', new CallableType()),
                            new FunctionParam(44, 'double', new UndefinedType()),
                            new FunctionParam(45, 'false', new UndefinedType()),
                            new FunctionParam(46, 'float', new FloatType()),
                            new FunctionParam(47, 'int', new IntType()),
                            new FunctionParam(48, 'integer', new UndefinedType()),
                            new FunctionParam(49, 'iterable', new IterableType()),
                            new FunctionParam(50, 'mixed', new UndefinedType()),
                            new FunctionParam(51, 'null', new UndefinedType()),
                            new FunctionParam(52, 'object', new UndefinedType()),
                            new FunctionParam(53, 'parent', new ParentType()),
                            new FunctionParam(54, 'resource', new UndefinedType()),
                            new FunctionParam(55, 'self', new SelfType()),
                            new FunctionParam(56, 'static', new UndefinedType()),
                            new FunctionParam(57, 'string', new StringType()),
                            new FunctionParam(58, 'true', new UndefinedType()),
                            new FunctionParam(59, 'undefined', new UndefinedType()),
                            new FunctionParam(60, 'typedArray', new ArrayType()),
                            new FunctionParam(61, 'nullableInt', new NullableType(new IntType())),
                        ],
                        new UndefinedType(),
                        62
                    ),
                    null
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
        static::assertFileExists($givenPath);

        $givenFile = new LocalFile($givenPath, new Ruleset(new Config()), new Config());
        $givenFile->parse();

        $actual = CodeElementDetector::detectFromTokens($givenFile, $givenUseReflection);

        self::assertEquals($expected, $actual);
    }
}
