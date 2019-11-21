<?php

namespace Gskema\TypeSniff\Sniffs\fixtures;

/**
 * Class TestClass5
 * @package Gskema\TypeSniff\Sniffs\fixtures
 */
class TestClass5
{
    /**
     * TestClass5 constructor.
     *  @param int $arg1
     */
    public function __construct(int $arg1)
    {
    }

    /**
     * @param string|null $arg1
     * @param float $arg2
     */
    public function func1(string $arg1 = null, float $arg2 = 1): void
    {

    }

    /**
     * @param float      $arg1
     * @param float      $arg2
     * @param float|null $arg3
     * @param double      $arg4
     * @param double      $arg5
     * @param double|null $arg6
     * @param float|double      $arg7
     * @param float|double      $arg8
     * @param float|double|null $arg9
     *
     * @return float
     */
    public function func2(
        $arg1 = 1,
        float $arg2 = -1,
        ?float $arg3 = -1,
        $arg4 = 1,
        float $arg5 = -1,
        ?float $arg6 = -1,
        $arg7 = 1,
        float $arg8 = -1,
        ?float $arg9 = -1
    ): float {
    }

    public function func3(string $arg1 = null): void
    {
    }

    /**
     * @param string   $name
     * @param mixed|null $default
     * @return mixed
     */
    private function func4(string $name, $default = null)
    {
    }

    /**
     * @param string   $name
     * @param int|null $default
     * @param null     $arg1
     *
     * @return mixed
     */
    private function func5(string $name, ?int $default = null, $arg1 = null)
    {
    }

    public function func6(?array $arg1): void
    {
    }

    /** @var string */
    const C1 = '';

    /** @var null */
    const C2 = null;

    /** @var null */
    public $prop1;

    /**
     * @return null
     */
    public function func7()
    {
    }

    /**
     * @return int|null
     */
    public function func8(): ?int
    {
    }

    /**
     * @param Collection|Image[] $images
     */
    public function func9(Collection $images): void
    {
    }

    /**
     * @see Something
     */
    const C3 = '?';

    /** @var array */
    const C4 = [];
}
