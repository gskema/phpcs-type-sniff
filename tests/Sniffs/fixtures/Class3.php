<?php

namespace Gskema\TypeSniff\Sniffs\fixtures;

class Class3 extends Class2
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
}
