<?php

namespace Gskema\TypeSniff\Core;

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Ruleset;
use ReflectionClass;

final class MockFile extends File
{
    public function __construct(string $contentOrClass)
    {
        $cfg = new Config();
        parent::__construct('', new Ruleset($cfg), $cfg);

        $content = class_exists($contentOrClass)
            ? file_get_contents((new ReflectionClass($contentOrClass))->getFileName())
            : $contentOrClass;
        $this->setContent($content);
        $this->process();
    }

    /**
     * @param int[]|string[] $tokenCodes
     * @return self
     */
    public static function fromTokenCodes(array $tokenCodes): self
    {
        $file = new self('');
        $file->setTokens(array_map(function ($tokenCode) {
            return ['code' => $tokenCode, 'type' => $tokenCode];
        }, $tokenCodes));

        return $file;
    }

    /**
     * @param mixed[] $tokens
     */
    public function setTokens(array $tokens): void
    {
        $this->tokens = $tokens;
        $this->numTokens = count($this->tokens);
    }
}
