<?php

namespace Gskema\TypeSniff\Sniffs\fixtures;

/**
 * Class TestClass9
 *
 * @package Gskema\TypeSniff\Sniffs\fixtures
 */
#[Attribute1]
#[Attribute2]
class TestClass9
{
    #[ArrayShape(['a' => 'int'])]
    public const CONST1 = [];

    /**
     * Description
     */
    #[ArrayShape(['a' => 'int'])]
    public const CONST2 = [];

    /**
     * Description
     * @var string
     */
    #[ArrayShape(['a' => 'int'])]
    public const CONST3 = [];

    /**
     * @var mixed[]|null
     */
    #[ArrayShape(['a' => 'int'])]
    public const CONST4 = [];

    #[ArrayShape(['a' => 'int'])]
    public $prop1 = [];

    /**
     * Description
     */
    #[ArrayShape(['a' => 'int'])]
    public $prop2;

    /**
     * Description
     * @var mixed[]|null
     */
    #[ArrayShape(['a' => 'int'])]
    public $prop3;

    #[ArrayShape(['a' => 'int'])]
    public function method1()
    {
        return [];
    }

    /**
     * @param array<int, int> $param1
     * @return array<int, int> Descritopn
     */
    public function method2(
        int $a,
        ?string $b,
        #[ArrayShape(['a' => 'int'])]
        array $c,
        #[ArrayShape(['a' => 'int'])]
        $d
    ) {
        return [$a, $b, $c, $d];
    }

    /** @var ?int */
    public $prop4;

    /** @var ?int|string */
    public $prop5;

    /**
     * @param ?string $a
     * @return ?int
     */
    public function method3(?string $a): ?int
    {
    }

    #[ArrayShape(['a' => 'int'])]
    public function method4(): int
    {
        return 1;
    }

    /**
     * @param mixed[]|null $a
     */
    public function method5(
        #[ArrayShape(['a' => 'int'])]
        ?array $a
    ): void {
    }

    /**
     * @return mixed[]|null $a
     */
    #[ArrayShape(['a' => 'int'])]
    public function method6(): ?array
    {
    }
}
