<?php

namespace Gskema\TypeSniff\Core\CodeElement\fixtures;

class TestClass3 extends \stdClass
{
    /**
     * @return void
     */
    public function method1(): void
    {
    }

    /**
     * @param array $array
     * @param bool $bool
     * @param boolean $boolean
     * @param callable $callable
     * @param double $double
     * @param false $false
     * @param float $float
     * @param int $int
     * @param integer $integer
     * @param iterable $iterable
     * @param mixed $mixed
     * @param null $null
     * @param object $object
     * @param parent $parent
     * @param resource $resource
     * @param self $self
     * @param static $static
     * @param string $string
     * @param true $true
     * @param $undefined
     * @param int[] $typedArray
     * @param int|null $nullableInt
     * @return $this
     */
    public function method2(
        array $array,
        bool $bool,
        $boolean,
        callable $callable,
        $double,
        $false,
        float $float,
        int $int,
        $integer,
        iterable $iterable,
        $mixed,
        $null,
        $object,
        parent $parent,
        $resource,
        self $self,
        $static,
        string $string,
        $true,
        $undefined,
        array $typedArray,
        ?int $nullableInt
    ) {
    }
}
