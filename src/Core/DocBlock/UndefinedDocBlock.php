<?php

namespace Gskema\TypeSniff\Core\DocBlock;

class UndefinedDocBlock extends DocBlock
{
    public function __construct()
    {
        parent::__construct([], []);
    }
}
