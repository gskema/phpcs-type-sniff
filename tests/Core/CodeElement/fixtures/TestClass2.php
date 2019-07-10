<?php

namespace Gskema\TypeSniff\Core\CodeElement\fixtures;

class TestClass3
{
    const C01 = null;
    const C02 = false;
    const C03 = true;
    const C04 = 1;
    const C05 = 1.1;
    const C06 = -1;
    const C07 = 'a';
    const C08 = "b";
    const C09 = <<<MUL
c
MUL;
    const C10 = [];
    const C11 = array(1, 2, 3);
    const C12 = self::C01;
    const C13 = SomeClass::CONST1;
    const C14    = /*comment*/2;
    public $prop1;
    public $prop2 = 1;
}
