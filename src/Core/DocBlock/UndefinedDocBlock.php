<?php

namespace Gskema\TypeSniff\Core\DocBlock;

/**
 * @see UndefinedDocBlockTest
 */
class UndefinedDocBlock extends DocBlock
{
    public function __construct()
    {
        parent::__construct([], []);
    }
}
