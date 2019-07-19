<?php

namespace Gskema\TypeSniff\Sniffs\fixtures;

/**
 * Class TestClass5
 * @package Gskema\TypeSniff\Sniffs\fixtures
 */
class TestClass5
{
    /**
     * TestClass5 constructor.
     * @param int $arg1
     */
    public function __construct(int $arg1)
    {
    }

    /**
     * @param string|null $arg1
     * @param float $arg2
     */
    public function func1(string $arg1 = null, float $arg2 = 1): void
    {

    }

    /**
     * @param float      $arg1
     * @param float      $arg2
     * @param float|null $arg3
     * @param double      $arg4
     * @param double      $arg5
     * @param double|null $arg6
     * @param float|double      $arg7
     * @param float|double      $arg8
     * @param float|double|null $arg9
     *
     * @return float
     */
    public function func2(
        $arg1 = 1,
        float $arg2 = -1,
        ?float $arg3 = -1,
        $arg4 = 1,
        float $arg5 = -1,
        ?float $arg6 = -1,
        $arg7 = 1,
        float $arg8 = -1,
        ?float $arg9 = -1
    ): float {
    }
}
