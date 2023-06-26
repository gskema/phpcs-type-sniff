<?php

namespace Gskema\TypeSniff\Core\Type;

use Gskema\TypeSniff\Core\Type\Common\ArrayType;
use Gskema\TypeSniff\Core\Type\Common\BoolType;
use Gskema\TypeSniff\Core\Type\Common\CallableType;
use Gskema\TypeSniff\Core\Type\Common\FalseType;
use Gskema\TypeSniff\Core\Type\Common\FloatType;
use Gskema\TypeSniff\Core\Type\Common\FqcnType;
use Gskema\TypeSniff\Core\Type\Common\IntType;
use Gskema\TypeSniff\Core\Type\Common\IterableType;
use Gskema\TypeSniff\Core\Type\Common\MixedType;
use Gskema\TypeSniff\Core\Type\Common\NullType;
use Gskema\TypeSniff\Core\Type\Common\ObjectType;
use Gskema\TypeSniff\Core\Type\Common\ParentType;
use Gskema\TypeSniff\Core\Type\Common\SelfType;
use Gskema\TypeSniff\Core\Type\Common\StaticType;
use Gskema\TypeSniff\Core\Type\Common\StringType;
use Gskema\TypeSniff\Core\Type\Common\UndefinedType;
use Gskema\TypeSniff\Core\Type\Common\UnionType;
use Gskema\TypeSniff\Core\Type\Common\VoidType;
use Gskema\TypeSniff\Core\Type\Declaration\NullableType;
use Gskema\TypeSniff\Core\Type\DocBlock\ClassStringType;
use Gskema\TypeSniff\Core\Type\DocBlock\DoubleType;
use Gskema\TypeSniff\Core\Type\DocBlock\KeyValueType;
use Gskema\TypeSniff\Core\Type\DocBlock\ResourceType;
use Gskema\TypeSniff\Core\Type\DocBlock\ThisType;
use Gskema\TypeSniff\Core\Type\DocBlock\TrueType;
use Gskema\TypeSniff\Core\Type\DocBlock\TypedArrayType;
use PHPUnit\Framework\TestCase;

class TypeTest extends TestCase
{
    public function test(): void
    {
        self::assertEquals('array', (new ArrayType())->toString());
        self::assertEquals('bool', (new BoolType())->toString());
        self::assertEquals('callable', (new CallableType())->toString());
        self::assertEquals('float', (new FloatType())->toString());
        self::assertEquals('CN1', (new FqcnType('CN1'))->toString());
        self::assertEquals('int', (new IntType())->toString());
        self::assertEquals('iterable', (new IterableType())->toString());
        self::assertEquals('object', (new ObjectType())->toString());
        self::assertEquals('parent', (new ParentType())->toString());
        self::assertEquals('resource', (new ResourceType())->toString());
        self::assertEquals('self', (new SelfType())->toString());
        self::assertEquals('string', (new StringType())->toString());
        self::assertEquals('', (new UndefinedType())->toString());
        self::assertEquals('void', (new VoidType())->toString());

        self::assertEquals('?string', (new NullableType(new StringType()))->toString());
        self::assertEquals(new StringType(), (new NullableType(new StringType()))->getType());
        self::assertEquals(true, (new NullableType(new StringType()))->containsType(StringType::class));
        self::assertEquals(false, (new NullableType(new StringType()))->containsType(VoidType::class));
        self::assertEquals('null|string', (new NullableType(new StringType()))->toDocString());
        self::assertEquals('int|null', (new NullableType(new IntType()))->toDocString());

        self::assertEquals('int|string', (new UnionType([new StringType(), new IntType()]))->toString());
        self::assertEquals([new StringType(), new IntType()], (new UnionType([new StringType(), new IntType()]))->getTypes());
        self::assertEquals(2, (new UnionType([new StringType(), new IntType()]))->getCount());
        self::assertEquals(true, (new UnionType([new StringType(), new IntType()]))->containsType(IntType::class));
        self::assertEquals(false, (new UnionType([new StringType(), new IntType()]))->containsType(SelfType::class));
        self::assertEquals([new StringType()], (new UnionType([new StringType(), new IntType()]))->getType(StringType::class));
        self::assertEquals(
            '(int|string)[]',
            (new TypedArrayType(new UnionType([new StringType(), new IntType()]), 1))
                ->toString(),
        );

        self::assertEquals('double', (new DoubleType())->toString());
        self::assertEquals('false', (new FalseType())->toString());
        self::assertEquals('mixed', (new MixedType())->toString());
        self::assertEquals('null', (new NullType())->toString());
        self::assertEquals('static', (new StaticType())->toString());
        self::assertEquals('$this', (new ThisType())->toString());
        self::assertEquals('true', (new TrueType())->toString());

        self::assertEquals('string[][]', (new TypedArrayType(new StringType(), 2))->toString());
        self::assertEquals(new StringType(), (new TypedArrayType(new StringType(), 2))->getType());
        self::assertEquals(2, (new TypedArrayType(new StringType(), 2))->getDepth());

        self::assertEquals('class-string', (new ClassStringType())->toString());
        self::assertEquals('iterable<?>', (new KeyValueType(new IterableType()))->toString());
    }
}
