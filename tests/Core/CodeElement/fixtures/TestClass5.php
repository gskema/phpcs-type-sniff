<?php

namespace Gskema\TypeSniff\Core\CodeElement\fixtures;

class TestClass5
{
    protected $prop1;
    protected $prop2;
    protected $prop3;
    protected $prop4;

    public function __construct()
    {
        $this->prop1 = null;
        $this->prop2 = 5; $this->prop2 = 5;
        $this->prop3 = array_slice([], 0, 1);
        $this->prop4 = null === 1 ? 0 : 1;
    }

    public function getProp1(): int
    {
        return $this->prop1;
    }

    public function getProp2(): int
    {
        return $this->prop2 ? 1 : 0;
    }

    public function getProp3(): int
    {
        // comment
        return
            // comment
            $this  // comment
         /* ??? */     -> // comment
                prop3  // comment
            ;  // comment
    }

    public function getProp4(): int
    {
        $a = 1;
        return $this->prop4;
    }

    public function method3(): void
    {
        $this->getProp4();
        $this->getProp4(1, 2);

        $a = [];
        array_walk($a, [$this, 'method3']);
    }
}
