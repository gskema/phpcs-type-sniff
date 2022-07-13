<?php

function func1(
    int $arg1 = self::CONST1,
    string $arg2 = SomeClass::CONST1
): int| false
{
}

class Acme {
    public function __construct(
        public int $arg1,
        $arg2,
        /** @var int[]|int|false */
        protected array|int|false $arg3 = false,
        protected ?bool $arg4 = false,
        protected null|int|string $arg5 = null,
    ) {
    }
}
