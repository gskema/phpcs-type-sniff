<?php

namespace Gskema\TypeSniff\Sniffs\fixtures;

class TestClass12
{
    /**
     * @param $prop1
     * @param int $prop2
     * @param array $prop3
     * @param int[] $prop4
     * @param $prop5
     * @param int $prop6
     * @param array $prop7
     * @param int[] $prop8
     * @param $prop10
     */
    public function __construct(
        $prop1,
        int $prop2,
        array $prop3,
        array $prop4,
        public $prop5,
        public int $prop6,
        public array $prop7,
        /** @var int[] */
        public array $prop8,
        /** @var int[] */
        public array $prop9,
        public array $prop10
    ) {
    }
}
