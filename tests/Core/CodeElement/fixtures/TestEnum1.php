<?php

namespace Gskema\TypeSniff\Sniffs\fixtures;

enum TestEnum1: int implements TestInterface8
{
    case TEST_CASE_0 = 0;
    case TEST_CASE_1 = 1;

    public function testMethod()
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

    public function method1()
    {
        $a = self::from(1);
        $b = self::cases();

        return self::TEST_CASE_1->name . self::TEST_CASE_0->value;
    }
}
