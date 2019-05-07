<?php

namespace Gskema\TypeSniff\Core\DocBlock\Tag;

use Gskema\TypeSniff\Core\Type\Common\SelfType;
use Gskema\TypeSniff\Core\Type\Common\StringType;
use Gskema\TypeSniff\Core\Type\Common\UndefinedType;
use PHPUnit\Framework\TestCase;

class TagTest extends TestCase
{
    public function test(): void
    {
        $genericTag = new GenericTag(1, 'see', 'content1');
        self::assertEquals(1, $genericTag->getLine());
        self::assertEquals('see', $genericTag->getName());
        self::assertEquals('content1', $genericTag->getContent());

        $paramTag = new ParamTag(2, new UndefinedType(), 'param1', 'desc1');
        self::assertEquals(2, $paramTag->getLine());
        self::assertEquals('param', $paramTag->getName());
        self::assertEquals(new UndefinedType(), $paramTag->getType());
        self::assertEquals('desc1', $paramTag->getDescription());
        self::assertEquals(true, $paramTag->hasDescription());
        self::assertEquals('param1', $paramTag->getParamName());

        $returnTag = new ReturnTag(3, new StringType(), null);
        self::assertEquals(3, $returnTag->getLine());
        self::assertEquals('return', $returnTag->getName());
        self::assertEquals(null, $returnTag->getDescription());
        self::assertEquals(false, $returnTag->hasDescription());
        self::assertEquals(new StringType(), $returnTag->getType());

        $varTag = new VarTag(4, new SelfType(), 'param2', 'desc2');
        self::assertEquals(4, $varTag->getLine());
        self::assertEquals('var', $varTag->getName());
        self::assertEquals(new SelfType(), $varTag->getType());
        self::assertEquals('desc2', $varTag->getDescription());
        self::assertEquals(true, $varTag->hasDescription());
        self::assertEquals('param2', $varTag->getParamName());
    }
}
