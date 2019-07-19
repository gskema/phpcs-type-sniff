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
}
