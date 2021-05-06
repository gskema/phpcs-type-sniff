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
     * @param int     $a
     * @param string  $b
     * @param mixed[] $c
     */
    #[ArrayShape(['a' => 'int'])]
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
}
