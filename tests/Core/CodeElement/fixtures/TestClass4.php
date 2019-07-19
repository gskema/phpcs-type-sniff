<?php

namespace Gskema\TypeSniff\Core\CodeElement\fixtures;

use stdClass;

class TestClass4 extends stdClass
{
    const C1 = [];
    const C2 = false;
    const C3 = 1.00;
    const C4 = 1;
    const C5 = '';
    const C6 = null;
    const C7 = array(1, 2, 3);
    const C8 = self::C4;
    const C9 = <<<MUL
c
MUL;

    public function func1(
        array $arg1 = [1, 2, 3],
        bool $arg2 = false,
        callable $arg3 = null,
        float $arg4 = 1.00,
        stdClass $arg5 = null,
        int $arg6 = 100,
        iterable $arg7 = [],
        parent $arg8 = null,
        self $arg9 = null,
        string $arg10 = '',
        $arg11 = null,
        ?string $arg12 = null,
        //
        array $arg13 = self::C1,
        bool $arg14 = self::C2,
        float $arg15 = self::C3,
        int $arg16 = self::C4,
        string $arg17 = self::C5,
        ?string $arg18 = self::C6,
        array $arg19 = self::C7,
        ?int $arg20 = self::C8,
        string $arg21 = self::C9,
        //
        array $arg22 = array(1, 2, 3),
        bool $arg23 = true,
        $arg24 = PHP_INT_MAX
    ): void {
    }
}
