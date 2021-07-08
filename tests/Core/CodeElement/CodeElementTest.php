<?php

namespace Gskema\TypeSniff\Core\CodeElement;

use Gskema\TypeSniff\Core\CodeElement\Element\ClassConstElement;
use Gskema\TypeSniff\Core\CodeElement\Element\ClassElement;
use Gskema\TypeSniff\Core\CodeElement\Element\ClassMethodElement;
use Gskema\TypeSniff\Core\CodeElement\Element\ClassPropElement;
use Gskema\TypeSniff\Core\CodeElement\Element\ConstElement;
use Gskema\TypeSniff\Core\CodeElement\Element\FileElement;
use Gskema\TypeSniff\Core\CodeElement\Element\FunctionElement;
use Gskema\TypeSniff\Core\CodeElement\Element\InterfaceConstElement;
use Gskema\TypeSniff\Core\CodeElement\Element\InterfaceElement;
use Gskema\TypeSniff\Core\CodeElement\Element\InterfaceMethodElement;
use Gskema\TypeSniff\Core\CodeElement\Element\Metadata\ClassMethodMetadata;
use Gskema\TypeSniff\Core\CodeElement\Element\TraitElement;
use Gskema\TypeSniff\Core\CodeElement\Element\TraitMethodElement;
use Gskema\TypeSniff\Core\CodeElement\Element\TraitPropElement;
use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\DocBlock\UndefinedDocBlock;
use Gskema\TypeSniff\Core\Func\FunctionParam;
use Gskema\TypeSniff\Core\Func\FunctionSignature;
use Gskema\TypeSniff\Core\Type\Common\IntType;
use Gskema\TypeSniff\Core\Type\Common\StringType;
use Gskema\TypeSniff\Core\Type\Common\UndefinedType;
use PHPUnit\Framework\TestCase;

class CodeElementTest extends TestCase
{
    public function test(): void
    {
        $classConst = new ClassConstElement(1, $this->createDocBlock(), 'FQCN1', 'CONST1', new IntType(), ['aaa']);
        self::assertEquals('CONST1', $classConst->getConstName());
        self::assertEquals('FQCN1', $classConst->getFqcn());
        self::assertEquals($this->createDocBlock(), $classConst->getDocBlock());
        self::assertEquals(1, $classConst->getLine());
        self::assertEquals(new IntType(), $classConst->getValueType());
        self::assertEquals(['aaa'], $classConst->getAttributeNames());

        $class = new ClassElement(2, $this->createDocBlock(), 'FQCN2', false);
        $class->setAttributeNames(['a']);
        self::assertEquals('FQCN2', $class->getFqcn());
        self::assertEquals($this->createDocBlock(), $class->getDocBlock());
        self::assertEquals(2, $class->getLine());
        self::assertEquals(['a'], $class->getAttributeNames());
        self::assertEquals(false, $class->isExtended());

        $classMethod = new ClassMethodElement(
            $this->createDocBlock(),
            'FQCN3',
            $this->createSignature(),
            new ClassMethodMetadata(['prop1', 'prop2'], 'prop3', ['method'], false),
            ['a']
        );
        $classMethod->getMetadata()->setExtended(false);
        self::assertEquals(3, $classMethod->getLine());
        self::assertEquals($this->createDocBlock(), $classMethod->getDocBlock());
        self::assertEquals('FQCN3', $classMethod->getFqcn());
        self::assertEquals($this->createSignature(), $classMethod->getSignature());
        self::assertEquals(false, $classMethod->getMetadata()->isExtended());
        self::assertEquals(true, $classMethod->getMetadata()->isBasicGetter());
        self::assertEquals('prop3', $classMethod->getMetadata()->getBasicGetterPropName());
        self::assertEquals(['prop1', 'prop2'], $classMethod->getMetadata()->getNonNullAssignedProps());
        self::assertEquals(['method'], $classMethod->getMetadata()->getThisMethodCalls());
        self::assertEquals(['a'], $classMethod->getAttributeNames());

        $classProp = new ClassPropElement(4, $this->createDocBlock(), 'FQCN4', 'prop1', new IntType(), new IntType());
        $classProp->setAttributeNames(['a']);
        self::assertEquals('FQCN4', $classProp->getFqcn());
        self::assertEquals($this->createDocBlock(), $classProp->getDocBlock());
        self::assertEquals(4, $classProp->getLine());
        self::assertEquals('prop1', $classProp->getPropName());
        self::assertEquals(new IntType(), $classProp->getType());
        self::assertEquals(new IntType(), $classProp->getDefaultValueType());
        self::assertEquals(['a'], $classProp->getAttributeNames());

        $const = new ConstElement(5, $this->createDocBlock(), 'NS1', 'CONST1', new IntType(), ['a']);
        self::assertEquals(5, $const->getLine());
        self::assertEquals($this->createDocBlock(), $const->getDocBlock());
        self::assertEquals('CONST1', $const->getName());
        self::assertEquals('NS1', $const->getNamespace());
        self::assertEquals(new IntType(), $const->getValueType());
        self::assertEquals(['a'], $const->getAttributeNames());

        $file = new FileElement(6, new UndefinedDocBlock(), 'path1');
        self::assertEquals(new UndefinedDocBlock(), $file->getDocBlock());
        self::assertEquals(6, $file->getLine());
        self::assertEquals('path1', $file->getPath());
        self::assertEquals([], $file->getAttributeNames());

        $func = new FunctionElement(7, $this->createDocBlock(), 'NS2', $this->createSignature(), ['a']);
        self::assertEquals(7, $func->getLine());
        self::assertEquals($this->createDocBlock(), $func->getDocBlock());
        self::assertEquals('NS2', $func->getNamespace());
        self::assertEquals($this->createSignature(), $func->getSignature());
        self::assertEquals(['a'], $func->getAttributeNames());

        $interfaceConst = new InterfaceConstElement(8, $this->createDocBlock(), 'FQCN5', 'CONST3', new IntType(), ['a']);
        self::assertEquals('CONST3', $interfaceConst->getConstName());
        self::assertEquals('FQCN5', $interfaceConst->getFqcn());
        self::assertEquals($this->createDocBlock(), $interfaceConst->getDocBlock());
        self::assertEquals(8, $interfaceConst->getLine());
        self::assertEquals(new IntType(), $interfaceConst->getValueType());
        self::assertEquals(['a'], $interfaceConst->getAttributeNames());

        $interface = new InterfaceElement(9, $this->createDocBlock(), 'FQCN6');
        $interface->setAttributeNames(['a']);
        self::assertEquals('FQCN6', $interface->getFqcn());
        self::assertEquals($this->createDocBlock(), $interface->getDocBlock());
        self::assertEquals(9, $interface->getLine());
        self::assertEquals([], $interface->getConstants());
        self::assertEquals([], $interface->getMethods());
        self::assertEquals(['a'], $interface->getAttributeNames());

        $interfaceMethod = new InterfaceMethodElement($this->createDocBlock(), 'FQCN7', $this->createSignature());
        $interfaceMethod->getMetadata()->setExtended(true);
        $interfaceMethod->setAttributeNames(['a']);
        self::assertEquals(3, $interfaceMethod->getLine());
        self::assertEquals($this->createDocBlock(), $interfaceMethod->getDocBlock());
        self::assertEquals('FQCN7', $interfaceMethod->getFqcn());
        self::assertEquals($this->createSignature(), $interfaceMethod->getSignature());
        self::assertEquals(true, $interfaceMethod->getMetadata()->isExtended());
        self::assertEquals(['a'], $interfaceMethod->getAttributeNames());

        $trait = new TraitElement(10, $this->createDocBlock(), 'FQCN8');
        $trait->setAttributeNames(['a']);
        self::assertEquals('FQCN8', $trait->getFqcn());
        self::assertEquals($this->createDocBlock(), $trait->getDocBlock());
        self::assertEquals(10, $trait->getLine());
        self::assertEquals([], $trait->getProperties());
        self::assertEquals([], $trait->getMethods());
        self::assertEquals(null, $trait->getOwnConstructor());
        self::assertEquals(null, $trait->getMethod('who'));
        self::assertEquals(['a'], $trait->getAttributeNames());

        $traitMethod = new TraitMethodElement($this->createDocBlock(), 'FQCN9', $this->createSignature());
        $traitMethod->getMetadata()->setExtended(true);
        $traitMethod->setAttributeNames(['a']);
        self::assertEquals(3, $traitMethod->getLine());
        self::assertEquals($this->createDocBlock(), $traitMethod->getDocBlock());
        self::assertEquals('FQCN9', $traitMethod->getFqcn());
        self::assertEquals($this->createSignature(), $traitMethod->getSignature());
        self::assertEquals(true, $traitMethod->getMetadata()->isExtended());
        self::assertEquals(['a'], $traitMethod->getAttributeNames());

        $traitProp = new TraitPropElement(12, $this->createDocBlock(), 'FQCN10', 'prop2', new IntType(), new IntType());
        $traitProp->setAttributeNames(['a']);
        self::assertEquals('FQCN10', $traitProp->getFqcn());
        self::assertEquals($this->createDocBlock(), $traitProp->getDocBlock());
        self::assertEquals(12, $traitProp->getLine());
        self::assertEquals('prop2', $traitProp->getPropName());
        self::assertEquals(new IntType(), $traitProp->getDefaultValueType());
        self::assertEquals(new IntType(), $traitProp->getDefaultValueType());
        self::assertEquals(['a'], $traitProp->getAttributeNames());
    }

    private function createSignature(): FunctionSignature
    {
        return new FunctionSignature(
            3,
            'method1',
            [new FunctionParam(20, 'arg1', new UndefinedType(), new UndefinedType(), [])],
            new StringType(),
            20
        );
    }

    private function createDocBlock(): DocBlock
    {
        return new DocBlock([1 => 'DescLine'], []);
    }
}
