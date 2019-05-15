<?php

namespace Gskema\TypeSniff\Core\DocBlock;

use Gskema\TypeSniff\Core\DocBlock\Tag\GenericTag;
use Gskema\TypeSniff\Core\DocBlock\Tag\ParamTag;
use Gskema\TypeSniff\Core\DocBlock\Tag\ReturnTag;
use Gskema\TypeSniff\Core\Type\Common\StringType;
use PHPUnit\Framework\TestCase;

class DocBlockTest extends TestCase
{
    public function test(): void
    {
        $descLines = [
            10 => 'Line1',
            11 => 'Line2',
        ];
        $tags = [
            new GenericTag(13, 'deprecated', null),
            new ParamTag(14, new StringType(), 'param1', 'desc1'),
            new ReturnTag(15, new StringType(), 'desc2'),
        ];
        $docBlock = new DocBlock($descLines, $tags);

        self::assertEquals(true, $docBlock->hasDescription());
        self::assertEquals($tags, $docBlock->getTags());
        self::assertEquals(
            [
                new GenericTag(13, 'deprecated', null),
            ],
            $docBlock->getTagsByName('deprecated')
        );
        self::assertEquals(
            [
                new ReturnTag(15, new StringType(), 'desc2'),
            ],
            $docBlock->getTagsByName('return')
        );
        self::assertEquals(
            new ReturnTag(15, new StringType(), 'desc2'),
            $docBlock->getReturnTag()
        );
        self::assertEquals(
            new ParamTag(14, new StringType(), 'param1', 'desc1'),
            $docBlock->getParamTag('param1')
        );
        self::assertEquals(
            null,
            $docBlock->getParamTag('param2')
        );
        self::assertEquals(
            [
                new ParamTag(14, new StringType(), 'param1', 'desc1'),
            ],
            $docBlock->getParamTags()
        );
        self::assertEquals(true, $docBlock->hasTag('deprecated'));
        self::assertEquals(false, $docBlock->hasTag('see'));
        self::assertEquals(true, $docBlock->hasOneOfTags(['deprecated', 'see', 'param']));
        self::assertEquals(false, $docBlock->hasOneOfTags(['see', 'link']));
        self::assertEquals($descLines, $docBlock->getDescriptionLines());
        self::assertEquals("Line1\nLine2", $docBlock->getDescription());
    }
}
