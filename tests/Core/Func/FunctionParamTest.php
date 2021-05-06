<?php

namespace Gskema\TypeSniff\Core\Func;

use Gskema\TypeSniff\Core\Type\Common\StringType;
use Gskema\TypeSniff\Core\Type\Common\UndefinedType;
use PHPUnit\Framework\TestCase;

final class FunctionParamTest extends TestCase
{
    public function test(): void
    {
        $param = new FunctionParam(1, 'param1', new StringType(), new UndefinedType(), ['aaa']);

        self::assertEquals(1, $param->getLine());
        self::assertEquals('param1', $param->getName());
        self::assertEquals(new StringType(), $param->getType());
        self::assertEquals(['aaa'], $param->getAttributeNames());
        self::assertEquals(true, $param->hasAttribute('aaa'));
    }
}
