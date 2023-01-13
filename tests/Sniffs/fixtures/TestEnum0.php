<?php

namespace Gskema\TypeSniff\Sniffs\fixtures;

enum TestEnum0
{
    public const CONST1 = [];

    case TEST_CASE_0;
    case TEST_CASE_1;

    public function testMethod($arg1)
    {
        return match($this) {
            self::TEST_CASE_0 => 1,
            TestEnum0::TEST_CASE_1 => 2,
        };
    }

    public static function testMethod1(): self
    {
        return self::TEST_CASE_1;
    }
}
