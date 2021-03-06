<?php

namespace Gskema\TypeSniff\Sniffs\fixtures;

class TestClass1
{
    public function method1($a, array $b): void
    {
    }

    /**
     * @param $c
     */
    public function method2($c)
    {
    }

    /**
     * @param $d
     * @param $e
     * @param $f
     * @param mixed $g
     *
     * @return array
     */
    public function method3($d, int $e, array $f, $g): array
    {
    }

    /**
     * @param null $h
     * @param int|null $i
     * @return string[]|null
     */
    public function method5($h, $i)
    {
    }

    /**
     * @param int[]|array $j
     * @param array $k
     * @param $l
     * @param int[] $m
     * @param int $n
     * @param string $o
     * @param int $p
     * @return self
     */
    public function method6(
        array $j,
        array $k,
        array $l,
        array $m,
        ?int $n,
        int $o,
        ?string $p
    ): self
    {
    }

    /**
     * @param int         $a
     * @param string|null $b
     */
    public function method7(int $a, ?string $b): void
    {
    }

    /**
     * @SmartTemplate
     * @param int         $a
     * @param string|null $b
     */
    public function method8(int $a, ?string $b): void
    {
    }


    /**
     * @param int         $a
     * @param string[]|null $b
     */
    public function method9(int $a, ?array $b): void
    {
    }

    public $prop1;

    /**
     * @some-custom-tag
     * @param int $a
     */
    public function method10(int $a): void
    {
    }
}
