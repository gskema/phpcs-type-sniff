<?php
/**
 * File Doc Comment
 */

namespace Gskema\TypeSniff\Core\CodeElement\fixtures;

/**
 * Class TestClass0
 *
 * @package Gskema\TypeSniff\Core\DocBlock\fixtures
 */
class TestClass0
{
    const CONST1 = 1;

    /** @var int */
    const CONST2 = 3;

    protected $prop1;

    /**
     * @var int
     */
    protected $prop2;

    /** @var string|null */
    protected $prop3;

    public function __construct()
    {
    }

    public function method1(): string
    {
        return '';
    }

    /**
     * @param int $param1
     *
     * @return array|null
     */
    public function method2(int $param1): ?array
    {
        /** CommentInsideFunction0 */
        return [];
    }

    /** CommentInsideClass0 */
}

// CommentBelowClass0

/** CommentBelowClass1 */
