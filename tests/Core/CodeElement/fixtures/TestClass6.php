<?php

namespace Gskema\TypeSniff\Core\CodeElement\fixtures;

#[Attribute1(133)]
class TestClass6
{
    #[ConstAttr]
    #[FooAttribute(null)]
    private const FOO_CONST = 28;

    #[SomeoneElse\FooMethodAttribe]
    public function getFoo(
        #[FooClassAttribe(28)]
        $a,
        #[FooClassAttribe(28)]
        #[FooClassAttribe(29)]
        string $b,
        $c
    ): string {
    }
}
