<?php

namespace Gskema\TypeSniff\Core\DocBlock;

use PHPUnit\Framework\TestCase;

class UndefinedDocBlockTest extends TestCase
{
    public function test(): void
    {
        $docBlock = new UndefinedDocBlock();
        self::assertEquals([], $docBlock->getDescriptionLines());
        self::assertEquals(false, $docBlock->hasTag('var'));
        self::assertEquals([], $docBlock->getParamTags());
        self::assertEquals(false, $docBlock->hasOneOfTags(['var', 'see']));
        self::assertEquals(null, $docBlock->getReturnTag());
        self::assertEquals([], $docBlock->getTagsByName('param'));
        self::assertEquals(false, $docBlock->hasDescription());
        self::assertEquals([], $docBlock->getTags());
        self::assertEquals(null, $docBlock->getParamTag('param1'));
    }
}
