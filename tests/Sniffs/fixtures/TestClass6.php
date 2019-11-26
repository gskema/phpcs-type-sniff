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
     * @param list<string> $arg6
     */
    public function func1(
        array $arg2,
        array $arg3,
        array $arg4,
        array $arg5,
        array $arg6
    ): void {
    }
}
