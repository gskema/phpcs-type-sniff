<?php

namespace Gskema\TypeSniff\Core\Func;

use Gskema\TypeSniff\Core\Type\Common\StringType;
use PHPUnit\Framework\TestCase;

final class FunctionParamTest extends TestCase
{
    public function test(): void
    {
        $param = new FunctionParam(1, 'param1', new StringType());

        self::assertEquals(1, $param->getLine());
        self::assertEquals('param1', $param->getName());
        self::assertEquals(new StringType(), $param->getType());
    }
}
