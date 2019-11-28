<?php

namespace Gskema\TypeSniff\Sniffs\fixtures;

class TestClass6
{
    /**
     * @Route("/")
     */
    public function func1(int $arg1): int
    {
    }

    /**
     * @Param-Converted("/")
     */
    public function func2($arg1, array $arg2)
    {
    }
}
