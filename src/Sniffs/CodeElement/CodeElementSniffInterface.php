<?php

namespace Gskema\TypeSniff\Sniffs\CodeElement;

use PHP_CodeSniffer\Files\File;
use Gskema\TypeSniff\Core\CodeElement\Element\CodeElementInterface;

interface CodeElementSniffInterface
{
    /**
     * Parses and applies sniff configuration.
     *
     * @param mixed[] $config
     */
    public function configure(array $config): void;

    /**
     * Return class names of CodeElementInterface that this sniff processes.
     *
     * @return string[]
     */
    public function register(): array;

    /**
     * Processes code element and its DocBlock, then adds warnings/errors to PHPCS file.
     *
     * @param File                 $file
     * @param CodeElementInterface $element
     * @param CodeElementInterface $parentElement
     */
    public function process(File $file, CodeElementInterface $element, CodeElementInterface $parentElement): void;
}
