<?php

namespace Gskema\TypeSniff\Inspection\Subject;

use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\DocBlock\Tag\ReturnTag;
use Gskema\TypeSniff\Core\Type\Common\BoolType;
use Gskema\TypeSniff\Core\Type\Common\IntType;
use Gskema\TypeSniff\Core\Type\Common\StringType;
use Gskema\TypeSniff\Core\Type\Common\VoidType;
use PHPUnit\Framework\TestCase;

class AbstractTypeSubjectTest extends TestCase
{
    public function test(): void
    {
        $subject = new ParamTypeSubject(
            new IntType(),
            new StringType(),
            new BoolType(),
            1,
            2,
            'parameter X',
            new DocBlock([2 => 'line2'], [new ReturnTag(3, new VoidType(), null)])
        );

        $subject->addFnTypeWarning('warning1');
        $subject->addFnTypeWarning('warning2');

        $subject->addDocTypeWarning('warning3');
        $subject->addDocTypeWarning('warning4');

        static::assertEquals(new IntType(), $subject->getDocType());
        static::assertEquals(new StringType(), $subject->getFnType());
        static::assertEquals(new BoolType(), $subject->getValueType());
        static::assertEquals(1, $subject->getDocTypeLine());
        static::assertEquals(2, $subject->getFnTypeLine());
        static::assertEquals('parameter X', $subject->getName());
        static::assertEquals(
            new DocBlock([2 => 'line2'], [new ReturnTag(3, new VoidType(), null)]),
            $subject->getDocBlock()
        );
        static::assertEquals(['warning3', 'warning4'], $subject->getDocTypeWarnings());
        static::assertEquals(['warning1', 'warning2'], $subject->getFnTypeWarnings());
    }
}
