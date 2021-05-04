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

    /**
     * @return array<int, array<string, int>>|null Description
     */
    public function getRange1(): ?array
    {
        return [];
    }

    /**
     * @return null|array<int, array<string, int>>
     */
    public function getRange2(): ?array
    {
        return [];
    }

    /**
     * Description
     * @return mixed[]
     */
    #[ArrayShape(['foo' => 'int'])]
    public function getRange3(): array { return ['foo' => 1]; }

    #[Attr11]
    /**
     * Description
     * @return mixed[]
     */
    #[Attr5]
    #[ArrayShape(['foo' => 'int'])]
    public function getRange4(): array { return ['foo' => 1]; }
}
