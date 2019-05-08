<?php

namespace Gskema\TypeSniff\Core\Func;

use Gskema\TypeSniff\Core\Type\Common\BoolType;
use Gskema\TypeSniff\Core\Type\Common\IntType;
use Gskema\TypeSniff\Core\Type\Common\VoidType;
use PHPUnit\Framework\TestCase;

final class FunctionSignatureTest extends TestCase
{
    public function test(): void
    {
        $sig = new FunctionSignature(
            1,
            'fn1',
            [
                new FunctionParam(1, 'param1', new IntType()),
                new FunctionParam(1, 'param2', new BoolType())
            ],
            new VoidType(),
            4
        );

        self::assertEquals('fn1', $sig->getName());
        self::assertEquals(1, $sig->getLine());
        self::assertEquals(4, $sig->getReturnLine());
        self::assertEquals(new VoidType(), $sig->getReturnType());
        self::assertEquals(
            [
                new FunctionParam(1, 'param1', new IntType()),
                new FunctionParam(1, 'param2', new BoolType())
            ],
            $sig->getParams()
        );
        self::assertEquals(true, $sig->hasParam('param1'));
        self::assertEquals(false, $sig->hasParam('param3'));
        self::assertEquals(
            new FunctionParam(1, 'param1', new IntType()),
            $sig->getParam('param1')
        );
        self::assertEquals(
            null,
            $sig->getParam('param3')
        );
    }
}
