<?php

namespace Gskema\TypeSniff\Sniffs\fixtures;

class TestClass7
{
    /** @var int */
    protected $prop1;

    /** @var int */
    protected $prop2;

    /** @var int */
    protected $prop3;

    /** @var int */
    protected $prop4;

    public function __construct()
    {
        $this->prop1 = null;
        $this->prop2 = 1;
        $this->setProp3(1);
        $this->assign1();
    }

    public function setProp3(int $prop3): void
    {
        $this->prop3 = $prop3;
    }

    public function assign1(): void
    {
        $this->assign1(); // test recursion
        $this->assign2();
        $this->assign3(); // unknown method
    }

    public function assign2(): void
    {
        $this->prop4 = 1;
    }

    /**
     * @return resource
     */
    public function returnX()
    {
        return 1;
    }
}
