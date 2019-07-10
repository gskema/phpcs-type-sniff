<?php

namespace Gskema\TypeSniff\Sniffs\fixtures;

class TestClass3 extends TestClass2
{
    public function __construct($arg1)
    {
    }

    public function __call($name, $arguments)
    {
    }

    /**
     * @inheritDoc
     */
    public function func1(): void
    {
    }

    public function func2(): void
    {
    }

    /**
     * @param string $arg1
     */
    public function func3(?int $arg1): void
    {
    }

    /**
     * @return int Desc
     */
    public function func4(): int
    {
    }


    /**
     * @return string|float
     */
    public function func5(): ?int
    {
    }

    /**
     * Description
     * @return int
     */
    public function func6(): int
    {
    }


    /**
     * @param int $param1 description
     */
    public function func7(int $param1): void
    {
    }

    /**
     * @return int|string
     */
    public function func8(): int
    {
    }

    /**
     * @return int|null|string
     */
    public function func9(): ?int
    {
    }
}
