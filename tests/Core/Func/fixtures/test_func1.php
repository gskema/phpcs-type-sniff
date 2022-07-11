<?php

function func1(
    int $arg1 = self::CONST1,
    string $arg2 = SomeClass::CONST1
)
{
}

class Acme {
    public function __construct(
        public int $arg1,
        $arg2,
        /** @var int[] */
        protected array $arg3,
        protected ?bool $arg4 = false,
    ) {
    }
}
