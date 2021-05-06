<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\DocBlock\DocBlock;

interface CodeElementInterface
{
    public function getLine(): int;

    public function getDocBlock(): DocBlock;

    /**
     * @return string[]
     */
    public function getAttributeNames(): array;
}
