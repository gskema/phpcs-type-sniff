<?php

namespace Gskema\TypeSniff\Sniffs\fixtures;

class TestClass8
{
    /**
     * @var string|null
     */
    private $foo;

    public function __construct(?string $foo)
    {
        $this->foo = $foo;
    }

    /**
     * Description.
     */
    public function getFoo(): ?string
    {
        return $this->foo;
    }
}
