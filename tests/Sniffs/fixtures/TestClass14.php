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

    /** @var \IteratorAggregate&\Countable */
    public $prop1;
    public \Iterator&\Countable $prop2;
    /** @var string&int */
    public $prop3;

    /**@var \IteratorAggregate&\Countable */
    public const CONST1 = '';
    /** @var string&int */
    public const CONST2 = '';

    /**
     * @param IteratorAggregate&Countable $param1
     * @param string&int $param3
     * @return never
     */
    public function method6(
        $param1,
        \Iterator&\Countable $prop2,
        $param3
    ): never { exit; }

    /**
     * @param class-string $prop1
     * @return class-string
     */
    public function method7(string $prop1): string
    {
    }

    /**
     * @param class-string $prop1
     * @return class-string
     */
    public function method8($prop1)
    {
    }

    /** @var class-string */
    public $prop4;
}
