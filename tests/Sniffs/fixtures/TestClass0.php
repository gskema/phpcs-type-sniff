<?php

namespace Gskema\TypeSniff\Sniffs\fixtures;

class TestClass0
{
    const C1 = 1;

    /** @var array */
    const C2 = 2;

    const C3 = [];

    /** @var string[] */
    const C4 = [];

    private $prop1;

    /**
     * @see something
     */
    private $prop2;

    /** @var */
    private $prop3;

    /** @var array */
    private $prop4;

    /** @var array|string */
    private $prop5;

    /** @var string */
    private $prop6 = [];

    /** @var string[] */
    private $prop7 = [];

    /** @var int $prop8 */
    private $prop8 = 8;
}
