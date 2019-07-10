<?php

function func1( ) {}

function /** wtf */ func2($a, int &$b, ?string $c, self ...$d): array
{
}

function func3(
    ?\Space\Class1 $arg1,
    bool $arg2, // wtf
    $arg3 = false,
    int $arg4 = null,
    int $arg5 = \Space1\Class2::SOME_CONST
): \Space1\Class2 {
}

function ($arg1)
