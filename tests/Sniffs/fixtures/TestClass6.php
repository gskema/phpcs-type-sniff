<?php

namespace Gskema\TypeSniff\Sniffs\fixtures;

/**
 * @TODO (int|string)[]
 */
class TestClass6
{
    /** @var array<int, string> */
    const C2 = [];

    /** @var array{foo: string, bar: int} */
    const C3 = [];

    /** @var array{b:bool,d:string}[] */
    const C4 = [];

    /** @var array<int, string> */
    private $prop2 = [];

    /** @var array{foo: string, bar: int} */
    private $prop3 = [];

    /** @var array{b:bool,d:string}[] */
    private $prop4 = [];

    /**
     * @param array<int, string> $arg2
     * @param array{foo: string, bar: int} $arg3
     * @param array{b:bool,d:string}[] $arg4
     * @param array<int, string>[] $arg5
     * @param array('key1' => string, 'key2' => int) $arg6
     * @param array<int|string, string>[] $arg7
     * @param array<int|string, array<int>>|null $arg8
     * @param string|NodeList|Location|Node|(Node|NodeList|Location)[] $arg9
     * @param array<string,array<string,(int|string[])>> $arg10
     * @param array<string,array<string,(int|string[])>> $arg11
     */
    public function func1(
        array $arg2,
        array $arg3,
        array $arg4,
        array $arg5,
        array $arg6,
        array $arg7,
        ?array $arg8,
        $arg9,
        array $arg10,
        array $arg11
    ): void {
    }

    /**
     * @Route("/")
     */
    public function func2(int $arg1): int
    {
    }

    /**
     * @Param-Converted("/")
     */
    public function func3($arg1, array $arg2)
    {
    }
}
