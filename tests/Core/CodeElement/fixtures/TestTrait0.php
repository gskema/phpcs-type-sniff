<?php

namespace Gskema\TypeSniff\Core\CodeElement\fixtures;

trait TestTrait0
{
    public $prop1 = 1;

    public function __construct(
        public int $prop2
    ) {
    }

    public function func1(int $arg1): int
    {
    }
}
