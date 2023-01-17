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

function func2(null $arg1 = null): null {}
function func3(false $arg1 = false): false {}
function func4(true $arg1 = true): true {}
function func5(false|null $arg1 = null): false|null {}
function func6(true|null $arg1 = true): true|null {}
function func7((\Iterator&\Countable)|null &$arg1): (\Iterator&\Countable)|string|null {}
