<?php

namespace Gskema\TypeSniff\Sniffs\fixtures;

class TestClass14
{
    /**
     * @return never
     */
    public function method1()
    {
    }

    /**
     * @return never
     */
    public function method2(): never
    {
    }

    /**
     * @return int|never
     */
    public function method3() // cannot use union
    {
    }

    public function method4(): never
    {
    }

    /**
     * @return int|never
     */
    public function method5(): never
    {
    }
}
