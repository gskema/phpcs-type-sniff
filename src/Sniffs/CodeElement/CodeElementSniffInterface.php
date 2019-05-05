<?php

namespace Gskema\TypeSniff\Sniffs\CodeElement;

use PHP_CodeSniffer\Files\File;
use Gskema\TypeSniff\Core\CodeElement\Element\CodeElementInterface;

interface CodeElementSniffInterface
{
    /**
     * @return string[]
     */
    public function register(): array;

    public function process(File $file, CodeElementInterface $codeElement): void;
}
