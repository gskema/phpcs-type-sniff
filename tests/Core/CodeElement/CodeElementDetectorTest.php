<?php

namespace Gskema\TypeSniff\Core\CodeElement;

use Gskema\TypeSniff\Core\CodeElement\Element\ClassConstElement;
use Gskema\TypeSniff\Core\CodeElement\Element\ClassElement;
use Gskema\TypeSniff\Core\CodeElement\Element\ClassMethodElement;
use Gskema\TypeSniff\Core\CodeElement\Element\ClassPropElement;
use Gskema\TypeSniff\Core\CodeElement\Element\ConstElement;
use Gskema\TypeSniff\Core\CodeElement\Element\EnumElement;
use Gskema\TypeSniff\Core\CodeElement\Element\EnumMethodElement;
use Gskema\TypeSniff\Core\CodeElement\Element\FileElement;
use Gskema\TypeSniff\Core\CodeElement\Element\FunctionElement;
use Gskema\TypeSniff\Core\CodeElement\Element\InterfaceConstElement;
use Gskema\TypeSniff\Core\CodeElement\Element\InterfaceElement;
use Gskema\TypeSniff\Core\CodeElement\Element\InterfaceMethodElement;
use Gskema\TypeSniff\Core\CodeElement\Element\Metadata\ClassMethodMetadata;
use Gskema\TypeSniff\Core\CodeElement\Element\Metadata\ClassPropMetadata;
use Gskema\TypeSniff\Core\CodeElement\Element\Metadata\TraitMethodMetadata;
use Gskema\TypeSniff\Core\CodeElement\Element\Metadata\TraitPropMetadata;
use Gskema\TypeSniff\Core\CodeElement\Element\TraitElement;
use Gskema\TypeSniff\Core\CodeElement\Element\TraitMethodElement;
use Gskema\TypeSniff\Core\CodeElement\Element\TraitPropElement;
use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\DocBlock\Tag\GenericTag;
use Gskema\TypeSniff\Core\DocBlock\Tag\ParamTag;
use Gskema\TypeSniff\Core\DocBlock\Tag\ReturnTag;
use Gskema\TypeSniff\Core\DocBlock\Tag\VarTag;
use Gskema\TypeSniff\Core\DocBlock\UndefinedDocBlock;
use Gskema\TypeSniff\Core\Func\FunctionParam;
use Gskema\TypeSniff\Core\Func\FunctionSignature;
use Gskema\TypeSniff\Core\Type\Common\ArrayType;
use Gskema\TypeSniff\Core\Type\Common\BoolType;
use Gskema\TypeSniff\Core\Type\Common\CallableType;
use Gskema\TypeSniff\Core\Type\Common\FalseType;
use Gskema\TypeSniff\Core\Type\Common\FloatType;
use Gskema\TypeSniff\Core\Type\Common\FqcnType;
use Gskema\TypeSniff\Core\Type\Common\IntType;
use Gskema\TypeSniff\Core\Type\Common\IterableType;
use Gskema\TypeSniff\Core\Type\Common\MixedType;
use Gskema\TypeSniff\Core\Type\Common\NullType;
use Gskema\TypeSniff\Core\Type\Common\ObjectType;
use Gskema\TypeSniff\Core\Type\Common\ParentType;
use Gskema\TypeSniff\Core\Type\Common\SelfType;
use Gskema\TypeSniff\Core\Type\Common\StaticType;
use Gskema\TypeSniff\Core\Type\Common\StringType;
use Gskema\TypeSniff\Core\Type\Common\TrueType;
use Gskema\TypeSniff\Core\Type\Common\UndefinedType;
use Gskema\TypeSniff\Core\Type\Common\UnionType;
use Gskema\TypeSniff\Core\Type\Common\VoidType;
use Gskema\TypeSniff\Core\Type\Declaration\NullableType;
use Gskema\TypeSniff\Core\Type\DocBlock\DoubleType;
use Gskema\TypeSniff\Core\Type\DocBlock\ResourceType;
use Gskema\TypeSniff\Core\Type\DocBlock\ThisType;
use Gskema\TypeSniff\Core\Type\DocBlock\TypedArrayType;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\LocalFile;
use PHP_CodeSniffer\Ruleset;
use PHPUnit\Framework\TestCase;

class CodeElementDetectorTest extends TestCase
{
    /**
     * @return mixed[][]
     */
    public function dataDetectFromTokens(): array
    {
        $dataSets = [];

        // #0
        $dataSets[] = [
            'givenUseReflection' => false,
            'givenFile'          => __DIR__ . '/fixtures/TestClass0.php',
            'expected'           => new FileElement(
                1,
                new DocBlock([3 => 'File Doc Comment'], []),
                __DIR__ . '/fixtures/TestClass0.php',
                [],
                [],
                [
                    new ClassElement(
                        13,
                        new DocBlock([9 => 'Class TestClass0'], [
                            new GenericTag(11, 'package', 'Gskema\TypeSniff\Core\DocBlock\fixtures'),
                        ]),
                        'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestClass0',
                        [],
                        false,
                        [
                            new ClassConstElement(
                                15,
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestClass0',
                                [],
                                'CONST1',
                                new IntType(),
                            ),
                            new ClassConstElement(
                                18,
                                new DocBlock([], [
                                    new VarTag(17, new IntType(), null, null),
                                ]),
                                'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestClass0',
                                [],
                                'CONST2',
                                new IntType(),
                            ),
                        ],
                        [
                            new ClassPropElement(
                                20,
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestClass0',
                                [],
                                'prop1',
                                new UndefinedType(),
                                new UndefinedType(),
                                false,
                            ),
                            new ClassPropElement(
                                25,
                                new DocBlock([], [
                                    new VarTag(23, new IntType(), null, null),
                                ]),
                                'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestClass0',
                                [],
                                'prop2',
                                new UndefinedType(),
                                new UndefinedType(),
                                false,
                            ),
                            new ClassPropElement(
                                28,
                                new DocBlock([], [
                                    new VarTag(27, new UnionType([new StringType(), new NullType()]), null, null),
                                ]),
                                'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestClass0',
                                [],
                                'prop3',
                                new UndefinedType(),
                                new UndefinedType(),
                                false,
                            ),
                        ],
                        [
                            new ClassMethodElement(
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestClass0',
                                [],
                                new FunctionSignature(
                                    30,
                                    '__construct',
                                    [],
                                    new UndefinedType(),
                                    30,
                                ),
                                new ClassMethodMetadata([], null, []),
                            ),
                            new ClassMethodElement(
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestClass0',
                                [],
                                new FunctionSignature(34, 'method1', [], new StringType(), 34),
                                new ClassMethodMetadata([], null, []),
                            ),
                            new ClassMethodElement(
                                new DocBlock([], [
                                    new ParamTag(40, new IntType(), 'param1', null),
                                    new ReturnTag(42, new UnionType([new ArrayType(), new NullType()]), null),
                                ]),
                                'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestClass0',
                                [],
                                new FunctionSignature(
                                    44,
                                    'method2',
                                    [
                                        new FunctionParam(44, 'param1', new IntType(), new UndefinedType(), []),
                                    ],
                                    new NullableType(new ArrayType()),
                                    44,
                                ),
                                new ClassMethodMetadata([], null, []),
                            ),
                        ],
                    ),
                ],
                [],
                [],
            ),
        ];

        // #1
        $dataSets[] = [
            'givenUseReflection' => false,
            'givenFile'          => __DIR__ . '/fixtures/TestClass1.php',
            'expected'           => new FileElement(
                1,
                new UndefinedDocBlock(),
                __DIR__ . '/fixtures/TestClass1.php',
                [
                    new ConstElement(
                        11,
                        new UndefinedDocBlock(),
                        'Gskema\TypeSniff\\Core\\CodeElement\\fixtures',
                        'CONST1',
                        new IntType(),
                        [],
                    ),
                ],
                [
                    new FunctionElement(
                        7,
                        new UndefinedDocBlock(),
                        'Gskema\TypeSniff\\Core\\CodeElement\\fixtures',
                        new FunctionSignature(7, 'namedFunc', [], new UndefinedType(), 7),
                        [],
                    ),
                ],
                [
                    new ClassElement(
                        14,
                        new UndefinedDocBlock(),
                        'Gskema\TypeSniff\\Core\\CodeElement\\fixtures\\TestClass1',
                        [],
                        false,
                        [
                            new ClassConstElement(
                                59,
                                new UndefinedDocBlock(),
                                'Gskema\TypeSniff\\Core\\CodeElement\\fixtures\\TestClass1',
                                [],
                                'CONST2',
                                new IntType(),
                            ),
                        ],
                        [
                            new ClassPropElement(
                                57,
                                new DocBlock([], [
                                    new VarTag(56, new IntType(), null, null),
                                ]),
                                'Gskema\TypeSniff\\Core\\CodeElement\\fixtures\\TestClass1',
                                [],
                                'prop1',
                                new UndefinedType(),
                                new UndefinedType(),
                                false,
                            ),
                        ],
                        [
                            new ClassMethodElement(
                                new UndefinedDocBlock(),
                                'Gskema\TypeSniff\\Core\\CodeElement\\fixtures\\TestClass1',
                                [],
                                new FunctionSignature(16, 'method1', [], new UndefinedType(), 16),
                                new ClassMethodMetadata([], null, ['method1']),
                            ),
                        ],
                    ),
                ],
                [],
                [],
            ),
        ];

        // #2
        $dataSets[] = [
            'givenUseReflection' => false,
            'givenFile'          => __DIR__ . '/fixtures/TestClass2.php',
            'expected'           => new FileElement(
                1,
                new UndefinedDocBlock(),
                __DIR__ . '/fixtures/TestClass2.php',
                [],
                [],
                [
                    new ClassElement(
                        5,
                        new UndefinedDocBlock(),
                        'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass2',
                        [],
                        false,
                        [
                            new ClassConstElement(
                                7,
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass2',
                                [],
                                'C01',
                                new NullType(),
                            ),
                            new ClassConstElement(
                                8,
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass2',
                                [],
                                'C02',
                                new BoolType(),
                            ),
                            new ClassConstElement(
                                9,
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass2',
                                [],
                                'C03',
                                new BoolType(),
                            ),
                            new ClassConstElement(
                                10,
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass2',
                                [],
                                'C04',
                                new IntType(),
                            ),
                            new ClassConstElement(
                                11,
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass2',
                                [],
                                'C05',
                                new FloatType(),
                            ),
                            new ClassConstElement(
                                12,
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass2',
                                [],
                                'C06',
                                new IntType(),
                            ),
                            new ClassConstElement(
                                13,
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass2',
                                [],
                                'C07',
                                new StringType(),
                            ),
                            new ClassConstElement(
                                14,
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass2',
                                [],
                                'C08',
                                new StringType(),
                            ),
                            new ClassConstElement(
                                15,
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass2',
                                [],
                                'C09',
                                new StringType(),
                            ),
                            new ClassConstElement(
                                18,
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass2',
                                [],
                                'C10',
                                new ArrayType(),
                            ),
                            new ClassConstElement(
                                19,
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass2',
                                [],
                                'C11',
                                new ArrayType(),
                            ),
                            new ClassConstElement(
                                20,
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass2',
                                [],
                                'C12',
                                null,
                            ),
                            new ClassConstElement(
                                21,
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass2',
                                [],
                                'C13',
                                null,
                            ),
                            new ClassConstElement(
                                22,
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass2',
                                [],
                                'C14',
                                new IntType(),
                            ),
                        ],
                        [
                            new ClassPropElement(
                                23,
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass2',
                                [],
                                'prop1',
                                new UndefinedType(),
                                new UndefinedType(),
                                false,
                            ),
                            new ClassPropElement(
                                24,
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass2',
                                [],
                                'prop2',
                                new UndefinedType(),
                                new IntType(),
                                false,
                                new ClassPropMetadata(true),
                            ),
                        ],
                        [],
                    ),
                ],
                [],
                [],
            ),
        ];

        // #3
        $dataSets[] = [
            'givenUseReflection' => false,
            'givenFile'          => __DIR__ . '/fixtures/TestInterface0.php',
            'expected'           => new FileElement(
                1,
                new UndefinedDocBlock(),
                __DIR__ . '/fixtures/TestInterface0.php',
                [],
                [],
                [],
                [],
                [
                    new InterfaceElement(
                        5,
                        new UndefinedDocBlock(),
                        'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestInterface0',
                        [],
                        [
                            new InterfaceConstElement(7, new UndefinedDocBlock(), 'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestInterface0', [], 'C1', new IntType()),
                        ],
                        [
                            new InterfaceMethodElement(
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestInterface0',
                                [],
                                new FunctionSignature(9, 'func1', [], new VoidType(), 9)
                            ),
                        ],
                    ),
                ],
            ),
        ];

        // #4
        $dataSets[] = [
            'givenUseReflection' => false,
            'givenFile'          => __DIR__ . '/fixtures/TestTrait0.php',
            'expected'           => new FileElement(
                1,
                new UndefinedDocBlock(),
                __DIR__ . '/fixtures/TestTrait0.php',
                [],
                [],
                [],
                [
                    new TraitElement(
                        5,
                        new UndefinedDocBlock(),
                        'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestTrait0',
                        [],
                        [
                            new TraitPropElement(
                                7,
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestTrait0',
                                [],
                                'prop1',
                                new UndefinedType(),
                                new IntType(),
                                false,
                                new TraitPropMetadata(true),
                            ),
                            new TraitPropElement(
                                10,
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestTrait0',
                                [],
                                'prop2',
                                new IntType(),
                                new UndefinedType(),
                                true,
                                new TraitPropMetadata(false),
                            ),
                        ],
                        [
                            new TraitMethodElement(
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestTrait0',
                                [],
                                new FunctionSignature(
                                    9,
                                    '__construct',
                                    [
                                        new FunctionParam(10, 'prop2', new IntType(), new UndefinedType(), [], null, true),
                                    ],
                                    new UndefinedType(),
                                    11
                                ),
                                new TraitMethodMetadata([], null, []),
                            ),
                            new TraitMethodElement(
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestTrait0',
                                [],
                                new FunctionSignature(
                                    14,
                                    'func1',
                                    [
                                        new FunctionParam(14, 'arg1', new IntType(), new UndefinedType(), []),
                                    ],
                                    new IntType(),
                                    14
                                ),
                                new TraitMethodMetadata([], null, []),
                            ),
                        ],
                    ),
                ],
                [],
            ),

        ];

        // #5
        $dataSets[] = [
            'givenUseReflection' => true,
            'givenFile'          => __DIR__ . '/fixtures/TestRef2.php',
            'expected'           => new FileElement(
                1,
                new UndefinedDocBlock(),
                __DIR__ . '/fixtures/TestRef2.php',
                [],
                [],
                [
                    new ClassElement(
                        5,
                        new UndefinedDocBlock(),
                        'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestRef2',
                        [],
                        true,
                        [],
                        [],
                        [
                            new ClassMethodElement(
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestRef2',
                                [],
                                new FunctionSignature(
                                    7,
                                    'func0',
                                    [],
                                    new VoidType(),
                                    7,
                                ),
                                new ClassMethodMetadata([], null, [], true),
                            ),
                            new ClassMethodElement(
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestRef2',
                                [],
                                new FunctionSignature(
                                    11,
                                    'func1',
                                    [],
                                    new VoidType(),
                                    12,
                                ),
                                new ClassMethodMetadata([], null, [], true),
                            ),
                            new ClassMethodElement(
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestRef2',
                                [],
                                new FunctionSignature(
                                    15,
                                    'func2',
                                    [],
                                    new UndefinedType(),
                                    15,
                                ),
                                new ClassMethodMetadata([], null, [], false),
                            ),
                        ],
                    ),
                ],
                [],
                [],
            ),
        ];

        // #6
        $dataSets[] = [
            'givenUseReflection' => false,
            'givenFile'          => __DIR__ . '/fixtures/TestParse0.php',
            'expected'           => new FileElement(
                1,
                new UndefinedDocBlock(),
                __DIR__ . '/fixtures/TestParse0.php',
                [],
                [],
                [
                    new ClassElement(
                        5,
                        new UndefinedDocBlock(),
                        'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestParse0',
                        [],
                        false,
                        [],
                        [
                            new ClassPropElement(
                                11,
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestParse0',
                                [],
                                'prop1',
                                new UndefinedType(),
                                null, // failed to parse
                                false,
                            ),
                        ],
                        [
                            new ClassMethodElement(
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestParse0',
                                [],
                                new FunctionSignature(
                                    7,
                                    'func1',
                                    [],
                                    new VoidType(),
                                    7,
                                ),
                                new ClassMethodMetadata([], null, []),
                            ),
                        ],
                    ),
                ],
                [],
                [],
            ),

        ];

        // #7
        $dataSets[] = [
            'givenUseReflection' => true,
            'givenFile'          => __DIR__ . '/fixtures/TestParse1.php',
            'expected'           => new FileElement(
                1,
                new UndefinedDocBlock(),
                __DIR__ . '/fixtures/TestParse1.php',
                [],
                [],
                [
                    new ClassElement(
                        5,
                        new UndefinedDocBlock(),
                        'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestParse1',
                        [],
                        true,
                        [],
                        [],
                        [
                            new ClassMethodElement(
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestParse1',
                                [],
                                new FunctionSignature(
                                    7,
                                    'func1',
                                    [],
                                    new VoidType(),
                                    7,
                                ),
                                new ClassMethodMetadata([], null, [], false), // ParseError
                            ),
                        ],
                    ),
                ],
                [],
                [],
            ),
        ];

        // #8
        $dataSets[] = [
            'givenUseReflection' => false,
            'givenFile'          => __DIR__ . '/fixtures/TestClass3.php',
            'expected'           => new FileElement(
                1,
                new UndefinedDocBlock(),
                __DIR__ . '/fixtures/TestClass3.php',
                [],
                [],
                [
                    new ClassElement(
                        5,
                        new UndefinedDocBlock(),
                        'Gskema\\TypeSniff\\Core\CodeElement\\fixtures\\TestClass3',
                        [],
                        true,
                        [],
                        [],
                        [
                            new ClassMethodElement(
                                new DocBlock([], [
                                    new ReturnTag(8, new VoidType(), null),
                                ]),
                                'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestClass3',
                                [],
                                new FunctionSignature(
                                    10,
                                    'method1',
                                    [],
                                    new VoidType(),
                                    10,
                                ),
                                new ClassMethodMetadata([], null, []),
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
                                    new ParamTag(36, new UnionType([new IntType(), new NullType()]), 'nullableInt', null),
                                    new ReturnTag(37, new ThisType(), null),
                                ]),
                                'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestClass3',
                                [],
                                new FunctionSignature(
                                    39,
                                    'method2',
                                    [
                                        new FunctionParam(40, 'array', new ArrayType(), new UndefinedType(), []),
                                        new FunctionParam(41, 'bool', new BoolType(), new UndefinedType(), []),
                                        new FunctionParam(42, 'boolean', new UndefinedType(), new UndefinedType(), []),
                                        new FunctionParam(43, 'callable', new CallableType(), new UndefinedType(), []),
                                        new FunctionParam(44, 'double', new UndefinedType(), new UndefinedType(), []),
                                        new FunctionParam(45, 'false', new UndefinedType(), new UndefinedType(), []),
                                        new FunctionParam(46, 'float', new FloatType(), new UndefinedType(), []),
                                        new FunctionParam(47, 'int', new IntType(), new UndefinedType(), []),
                                        new FunctionParam(48, 'integer', new UndefinedType(), new UndefinedType(), []),
                                        new FunctionParam(49, 'iterable', new IterableType(), new UndefinedType(), []),
                                        new FunctionParam(50, 'mixed', new UndefinedType(), new UndefinedType(), []),
                                        new FunctionParam(51, 'null', new UndefinedType(), new UndefinedType(), []),
                                        new FunctionParam(52, 'object', new UndefinedType(), new UndefinedType(), []),
                                        new FunctionParam(53, 'parent', new ParentType(), new UndefinedType(), []),
                                        new FunctionParam(54, 'resource', new UndefinedType(), new UndefinedType(), []),
                                        new FunctionParam(55, 'self', new SelfType(), new UndefinedType(), []),
                                        new FunctionParam(56, 'static', new UndefinedType(), new UndefinedType(), []),
                                        new FunctionParam(57, 'string', new StringType(), new UndefinedType(), []),
                                        new FunctionParam(58, 'true', new UndefinedType(), new UndefinedType(), []),
                                        new FunctionParam(59, 'undefined', new UndefinedType(), new UndefinedType(), []),
                                        new FunctionParam(60, 'typedArray', new ArrayType(), new UndefinedType(), []),
                                        new FunctionParam(61, 'nullableInt', new NullableType(new IntType()), new UndefinedType(), []),
                                    ],
                                    new UndefinedType(),
                                    62,
                                ),
                                new ClassMethodMetadata([], null, []),
                            ),
                        ],
                    ),
                ],
                [],
                [],
            ),
        ];

        // #9
        $fqcn9 = 'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestClass4';
        $dataSets[] = [
            'givenUseReflection' => true,
            'givenFile'          => __DIR__ . '/fixtures/TestClass4.php',
            'expected'           => new FileElement(
                1,
                new UndefinedDocBlock(),
                __DIR__ . '/fixtures/TestClass4.php',
                [],
                [],
                [
                    new ClassElement(
                        7,
                        new UndefinedDocBlock(),
                        $fqcn9,
                        [],
                        true,
                        [
                            new ClassConstElement(9, new UndefinedDocBlock(), $fqcn9, [], 'C1', new ArrayType()),
                            new ClassConstElement(10, new UndefinedDocBlock(), $fqcn9, [], 'C2', new BoolType()),
                            new ClassConstElement(11, new UndefinedDocBlock(), $fqcn9, [], 'C3', new FloatType()),
                            new ClassConstElement(12, new UndefinedDocBlock(), $fqcn9, [], 'C4', new IntType()),
                            new ClassConstElement(13, new UndefinedDocBlock(), $fqcn9, [], 'C5', new StringType()),
                            new ClassConstElement(14, new UndefinedDocBlock(), $fqcn9, [], 'C6', new NullType()),
                            new ClassConstElement(15, new UndefinedDocBlock(), $fqcn9, [], 'C7', new ArrayType()),
                            new ClassConstElement(16, new UndefinedDocBlock(), $fqcn9, [], 'C8', null),
                            new ClassConstElement(17, new UndefinedDocBlock(), $fqcn9, [], 'C9', new StringType()),
                        ],
                        [],
                        [
                            new ClassMethodElement(
                                new UndefinedDocBlock(),
                                $fqcn9,
                                [],
                                new FunctionSignature(
                                    21,
                                    'func1',
                                    [
                                        new FunctionParam(22, 'arg1', new ArrayType(), new ArrayType(), []),
                                        new FunctionParam(23, 'arg2', new BoolType(), new FalseType(), []),
                                        new FunctionParam(24, 'arg3', new CallableType(), new NullType(), []),
                                        new FunctionParam(25, 'arg4', new FloatType(), new FloatType(), []),
                                        new FunctionParam(26, 'arg5', new FqcnType('stdClass'), new NullType(), []),
                                        new FunctionParam(27, 'arg6', new IntType(), new IntType(), []),
                                        new FunctionParam(28, 'arg7', new IterableType(), new ArrayType(), []),
                                        new FunctionParam(29, 'arg8', new ParentType(), new NullType(), []),
                                        new FunctionParam(30, 'arg9', new SelfType(), new NullType(), []),
                                        new FunctionParam(31, 'arg10', new StringType(), new StringType(), []),
                                        new FunctionParam(32, 'arg11', new UndefinedType(), new NullType(), []),
                                        new FunctionParam(33, 'arg12', new NullableType(new StringType()), new NullType(), []),

                                        new FunctionParam(35, 'arg13', new ArrayType(), null, []),
                                        new FunctionParam(36, 'arg14', new BoolType(), null, []),
                                        new FunctionParam(37, 'arg15', new FloatType(), null, []),
                                        new FunctionParam(38, 'arg16', new IntType(), null, []),
                                        new FunctionParam(39, 'arg17', new StringType(), null, []),
                                        new FunctionParam(40, 'arg18', new NullableType(new StringType()), null, []),
                                        new FunctionParam(41, 'arg19', new ArrayType(), null, []),
                                        new FunctionParam(42, 'arg20', new NullableType(new IntType()), null, []),
                                        new FunctionParam(43, 'arg21', new StringType(), null, []),
                                        new FunctionParam(45, 'arg22', new ArrayType(), new ArrayType(), []),
                                        new FunctionParam(46, 'arg23', new BoolType(), new TrueType(), []),
                                        new FunctionParam(47, 'arg24', new UndefinedType(), new IntType(), []),
                                    ],
                                    new VoidType(),
                                    48,
                                ),
                                new ClassMethodMetadata([], null, [], false),
                            ),
                        ],
                    ),
                ],
                [],
                [],
            ),
        ];

        // #10
        $fqcn10 = 'Gskema\\TypeSniff\\Core\\CodeElement\\fixtures\\TestClass5';
        $dataSets[] = [
            'givenUseReflection' => false,
            'givenFile'          => __DIR__ . '/fixtures/TestClass5.php',
            'expected'           => new FileElement(
                1,
                new UndefinedDocBlock(),
                __DIR__ . '/fixtures/TestClass5.php',
                [],
                [],
                [
                    new ClassElement(
                        5,
                        new UndefinedDocBlock(),
                        $fqcn10,
                        [],
                        false,
                        [],
                        [
                            new ClassPropElement(7, new UndefinedDocBlock(), $fqcn10, [], 'prop1', new UndefinedType(), new UndefinedType(), false),
                            new ClassPropElement(8, new UndefinedDocBlock(), $fqcn10, [], 'prop2', new UndefinedType(), new UndefinedType(), false),
                            new ClassPropElement(9, new UndefinedDocBlock(), $fqcn10, [], 'prop3', new UndefinedType(), new UndefinedType(), false),
                            new ClassPropElement(10, new UndefinedDocBlock(), $fqcn10, [], 'prop4', new UndefinedType(), new UndefinedType(), false),
                        ],
                        [
                            new ClassMethodElement(
                                new UndefinedDocBlock(),
                                $fqcn10,
                                [],
                                new FunctionSignature(
                                    12,
                                    '__construct',
                                    [],
                                    new UndefinedType(),
                                    12,
                                ),
                                new ClassMethodMetadata(['prop2', 'prop3', 'prop4'], null, []),
                            ),
                            new ClassMethodElement(
                                new UndefinedDocBlock(),
                                $fqcn10,
                                [],
                                new FunctionSignature(
                                    20,
                                    'getProp1',
                                    [],
                                    new IntType(),
                                    20,
                                ),
                                new ClassMethodMetadata([], 'prop1', []),
                            ),
                            new ClassMethodElement(
                                new UndefinedDocBlock(),
                                $fqcn10,
                                [],
                                new FunctionSignature(
                                    25,
                                    'getProp2',
                                    [],
                                    new IntType(),
                                    25,
                                ),
                                new ClassMethodMetadata([], null, []),
                            ),
                            new ClassMethodElement(
                                new UndefinedDocBlock(),
                                $fqcn10,
                                [],
                                new FunctionSignature(
                                    30,
                                    'getProp3',
                                    [],
                                    new IntType(),
                                    30,
                                ),
                                new ClassMethodMetadata([], 'prop3', []),
                            ),
                            new ClassMethodElement(
                                new UndefinedDocBlock(),
                                $fqcn10,
                                [],
                                new FunctionSignature(
                                    41,
                                    'getProp4',
                                    [],
                                    new IntType(),
                                    41,
                                ),
                                new ClassMethodMetadata([], null, []),
                            ),
                            new ClassMethodElement(
                                new UndefinedDocBlock(),
                                $fqcn10,
                                [],
                                new FunctionSignature(
                                    47,
                                    'method3',
                                    [],
                                    new VoidType(),
                                    47,
                                ),
                                new ClassMethodMetadata([], null, ['getProp4']),
                            ),
                        ],
                    ),
                ],
                [],
                [],
            ),
        ];

        // #11
        $dataSets[] = [
            'givenUseReflection' => true,
            'givenFile'          => __DIR__ . '/fixtures/TestRef3.php',
            'expected'           => new FileElement(
                1,
                new UndefinedDocBlock(),
                __DIR__ . '/fixtures/TestRef3.php',
                [],
                [],
                [
                    new ClassElement(
                        5,
                        new UndefinedDocBlock(),
                        'Gskema\TypeSniff\Core\CodeElement\fixtures\TestRef3',
                        [],
                        false,
                        [],
                        [],
                        [
                            new ClassMethodElement(
                                new UndefinedDocBlock(),
                                'Gskema\TypeSniff\Core\CodeElement\fixtures\TestRef3',
                                [],
                                new FunctionSignature(
                                    7,
                                    'testMethod',
                                    [],
                                    new UndefinedType(),
                                    7,
                                ),
                                new ClassMethodMetadata([], null, []),
                            ),
                        ],
                    ),
                ],
            ),
        ];

        // #12
        $dataSets[] = [
            'givenUseReflection' => true,
            'givenFile'          => __DIR__ . '/fixtures/TestClass6.php',
            'expected'           => new FileElement(
                1,
                new UndefinedDocBlock(),
                __DIR__ . '/fixtures/TestClass6.php',
                [],
                [],
                [
                    new ClassElement(
                        6,
                        new UndefinedDocBlock(),
                        'Gskema\TypeSniff\Core\CodeElement\fixtures\TestClass6',
                        ['Attribute1'],
                        false,
                        [
                            new ClassConstElement(
                                10,
                                new UndefinedDocBlock(),
                                'Gskema\TypeSniff\Core\CodeElement\fixtures\TestClass6',
                                ['ConstAttr', 'FooAttribute'],
                                'FOO_CONST',
                                new IntType(),
                            ),
                        ],
                        [],
                        [
                            new ClassMethodElement(
                                new UndefinedDocBlock(),
                                'Gskema\TypeSniff\Core\CodeElement\fixtures\TestClass6',
                                ['SomeoneElse\FooMethodAttribe'],
                                new FunctionSignature(
                                    13,
                                    'getFoo',
                                    [
                                        new FunctionParam(
                                            15,
                                            'a',
                                            new UndefinedType(),
                                            new UndefinedType(),
                                            ['FooClassAttribe'],
                                        ),
                                        new FunctionParam(
                                            18,
                                            'b',
                                            new StringType(),
                                            new UndefinedType(),
                                            ['FooClassAttribe'],
                                        ),
                                        new FunctionParam(
                                            19,
                                            'c',
                                            new UndefinedType(),
                                            new UndefinedType(),
                                            [],
                                        )
                                    ],
                                    new StringType(),
                                    20,
                                ),
                                new ClassMethodMetadata([], null, [], false),
                            ),
                        ],
                    ),
                ],
            ),
        ];

        // #13
        $dataSets[] = [
            'givenUseReflection' => true,
            'givenFile'          => __DIR__ . '/fixtures/TestEnum0.php',
            'expected' => new FileElement(
                1,
                new UndefinedDocBlock(),
                __DIR__ . '/fixtures/TestEnum0.php',
                [],
                [],
                [],
                [],
                [],
                [
                    new EnumElement(
                        5,
                        new UndefinedDocBlock(),
                        'Gskema\\TypeSniff\\Sniffs\\fixtures\\TestEnum0',
                        [],
                        [],
                        [
                            new EnumMethodElement(
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Sniffs\\fixtures\\TestEnum0',
                                [],
                                new FunctionSignature(
                                    10,
                                    'testMethod',
                                    [],
                                    new UndefinedType(),
                                    10
                                ),
                            ),
                            new EnumMethodElement(
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Sniffs\\fixtures\\TestEnum0',
                                [],
                                new FunctionSignature(
                                    18,
                                    'testMethod1',
                                    [],
                                    new SelfType(),
                                    18
                                ),
                            ),
                        ]
                    ),
                ],
            ),
        ];

        // #14
        $dataSets[] = [
            'givenUseReflection' => true,
            'givenFile' => __DIR__ . '/fixtures/TestEnum1.php',
            'expected' => new FileElement(
                1,
                new UndefinedDocBlock(),
                __DIR__ . '/fixtures/TestEnum1.php',
                [],
                [],
                [],
                [],
                [],
                [
                    new EnumElement(
                        5,
                        new UndefinedDocBlock(),
                        'Gskema\\TypeSniff\\Sniffs\\fixtures\\TestEnum1',
                        [],
                        [],
                        [
                            new EnumMethodElement(
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Sniffs\\fixtures\\TestEnum1',
                                [],
                                new FunctionSignature(
                                    10,
                                    'testMethod',
                                    [],
                                    new UndefinedType(),
                                    10
                                ),
                            ),
                            new EnumMethodElement(
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Sniffs\\fixtures\\TestEnum1',
                                [],
                                new FunctionSignature(
                                    18,
                                    'testMethod1',
                                    [],
                                    new SelfType(),
                                    18
                                ),
                            ),
                            new EnumMethodElement(
                                new UndefinedDocBlock(),
                                'Gskema\\TypeSniff\\Sniffs\\fixtures\\TestEnum1',
                                [],
                                new FunctionSignature(
                                    23,
                                    'method1',
                                    [],
                                    new UndefinedType(),
                                    23
                                ),
                            ),
                        ]
                    ),
                ],
            ),
        ];

        // #15
        $dataSets[] = [
            'givenUseReflection' => true,
            'givenFile' => __DIR__ . '/fixtures/TestClass7.php',
            'expected' => new FileElement(
                1,
                new UndefinedDocBlock(),
                __DIR__ . '/fixtures/TestClass7.php',
                [],
                [],
                [
                    new ClassElement(
                        5,
                        new UndefinedDocBlock(),
                        'Gskema\TypeSniff\Core\CodeElement\fixtures\TestClass7',
                        [],
                        false,
                        [],
                        [
                            new ClassPropElement(
                                7,
                                new UndefinedDocBlock(),
                                'Gskema\TypeSniff\Core\CodeElement\fixtures\TestClass7',
                                [],
                                'prop1',
                                new StringType(),
                                new UndefinedType(),
                                false,
                                new ClassPropMetadata(false)
                            ),
                            new ClassPropElement(
                                12,
                                new UndefinedDocBlock(),
                                'Gskema\TypeSniff\Core\CodeElement\fixtures\TestClass7',
                                [],
                                'prop3',
                                new FqcnType('\stdClass'),
                                new FqcnType('\stdClass'),
                                true,
                                new ClassPropMetadata(true)
                            ),
                            new ClassPropElement(
                                10,
                                new UndefinedDocBlock(),
                                'Gskema\TypeSniff\Core\CodeElement\fixtures\TestClass7',
                                [],
                                'title',
                                new UnionType([new StringType(), new IntType()]),
                                new UndefinedType(),
                                true,
                                new ClassPropMetadata(false)
                            ),
                        ],
                        [
                            new ClassMethodElement(
                                new UndefinedDocBlock(),
                                'Gskema\TypeSniff\Core\CodeElement\fixtures\TestClass7',
                                [],
                                new FunctionSignature(
                                    9,
                                    '__construct',
                                    [
                                        new FunctionParam(
                                            10,
                                            'title',
                                            new UnionType([new StringType(), new IntType()]),
                                            new UndefinedType(),
                                            [],
                                            new UndefinedDocBlock(),
                                            true
                                        ),
                                        new FunctionParam(
                                            11,
                                            'prop2',
                                            new CallableType(),
                                            new UndefinedType(),
                                            [],
                                            new UndefinedDocBlock(),
                                            false
                                        ),
                                        new FunctionParam(
                                            12,
                                            'prop3',
                                            new FqcnType('\stdClass'),
                                            new FqcnType('\stdClass'),
                                            [],
                                            new UndefinedDocBlock(),
                                            true
                                        )
                                    ],
                                    new UndefinedType(),
                                    13
                                ),
                                new ClassMethodMetadata([], '', [], false)
                            )
                        ],
                    ),
                ],
            ),
        ];

        return $dataSets;
    }

    /**
     * @dataProvider dataDetectFromTokens
     *
     * @param bool        $givenUseReflection
     * @param string      $givenPath
     * @param FileElement $expected
     */
    public function testDetectFromTokens(bool $givenUseReflection, string $givenPath, FileElement $expected): void
    {
        static::assertFileExists($givenPath);

        $givenFile = new LocalFile($givenPath, new Ruleset(new Config()), new Config());
        $givenFile->parse();

        $actual = CodeElementDetector::detectFromTokens($givenFile, $givenUseReflection);

        self::assertEquals($expected, $actual);
    }
}
