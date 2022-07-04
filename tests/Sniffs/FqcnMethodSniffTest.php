<?php

namespace Gskema\TypeSniff\Sniffs;

use Gskema\TypeSniff\Core\CodeElement\Element\AbstractFqcnMethodElement;
use Gskema\TypeSniff\Core\CodeElement\Element\ClassMethodElement;
use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\DocBlock\Tag\ParamTag;
use Gskema\TypeSniff\Core\DocBlock\Tag\ReturnTag;
use Gskema\TypeSniff\Core\Func\FunctionParam;
use Gskema\TypeSniff\Core\Func\FunctionSignature;
use Gskema\TypeSniff\Core\Type\Common\IntType;
use Gskema\TypeSniff\Core\Type\Common\UndefinedType;
use Gskema\TypeSniff\Core\Type\Common\VoidType;
use Gskema\TypeSniff\Sniffs\CodeElement\FqcnMethodSniff;
use PHPUnit\Framework\TestCase;

class FqcnMethodSniffTest extends TestCase
{
    /**
     * @return mixed[][]
     */
    public function dataHasUselessDocBlock(): array
    {
        $dataSets = [];

        // #0 useless
        $dataSets[] = [
            new ClassMethodElement(
                new DocBlock([], [
                    new ParamTag(1, new IntType(), 'param1', null),
                    new ReturnTag(2, new VoidType(), null),
                ]),
                '',
                new FunctionSignature(
                    4,
                    'func1',
                    [
                        new FunctionParam(3, 'param1', new IntType(), new UndefinedType(), []),
                    ],
                    new VoidType(),
                    5,
                ),
                null,
            ),
            true,
        ];

        // #1 missing param tag
        $dataSets[] = [
            new ClassMethodElement(
                new DocBlock([], [
                    new ReturnTag(2, new VoidType(), null),
                ]),
                '',
                new FunctionSignature(
                    4,
                    'func1',
                    [
                        new FunctionParam(3, 'param1', new IntType(), new UndefinedType(), []),
                    ],
                    new VoidType(),
                    5,
                ),
                null,
            ),
            false,
        ];

        // #2 has func description
        $dataSets[] = [
            new ClassMethodElement(
                new DocBlock([0 => 'description1'], [
                    new ParamTag(1, new IntType(), 'param1', null),
                    new ReturnTag(2, new VoidType(), null),
                ]),
                '',
                new FunctionSignature(
                    4,
                    'func1',
                    [
                        new FunctionParam(3, 'param1', new IntType(), new UndefinedType(), []),
                    ],
                    new VoidType(),
                    5,
                ),
                null,
            ),
            false,
        ];

        // #2 has param description
        $dataSets[] = [
            new ClassMethodElement(
                new DocBlock([], [
                    new ParamTag(1, new IntType(), 'param1', 'desc1'),
                    new ReturnTag(2, new VoidType(), null),
                ]),
                '',
                new FunctionSignature(
                    4,
                    'func1',
                    [
                        new FunctionParam(3, 'param1', new IntType(), new UndefinedType(), []),
                    ],
                    new VoidType(),
                    5,
                ),
                null,
            ),
            false,
        ];

        // #2 different types
        $dataSets[] = [
            new ClassMethodElement(
                new DocBlock([], [
                    new ParamTag(1, new IntType(), 'param1', 'desc1'),
                    new ReturnTag(2, new IntType(), null),
                ]),
                '',
                new FunctionSignature(
                    4,
                    'func1',
                    [
                        new FunctionParam(3, 'param1', new IntType(), new UndefinedType(), []),
                    ],
                    new VoidType(),
                    5,
                ),
                null,
            ),
            false,
        ];

        return $dataSets;
    }

    /**
     * @dataProvider dataHasUselessDocBlock
     *
     * @param AbstractFqcnMethodElement $givenMethod
     * @param bool                      $expectedResult
     */
    public function testHasUselessDocBlock(
        AbstractFqcnMethodElement $givenMethod,
        bool $expectedResult,
    ): void {
        $sniff = new class extends FqcnMethodSniff {
            public function hasUselessDocBlockPublic(AbstractFqcnMethodElement $method): bool
            {
                return parent::hasUselessDocBlock($method);
            }
        };

        $actualResult = $sniff->hasUselessDocBlockPublic($givenMethod);

        static::assertEquals($expectedResult, $actualResult);
    }
}
