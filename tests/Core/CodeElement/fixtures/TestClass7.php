<?php

namespace Gskema\TypeSniff\Core\CodeElement\fixtures;

class TestClass7
{
    public readonly string $prop1;

    public function __construct(
        public readonly string|int $title,
        callable $prop2,
        public readonly \stdClass $prop3 = new \stdClass(),
    ) {
        $prop2 = strpos(...);
    }
}
