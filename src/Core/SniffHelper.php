<?php

namespace Gskema\TypeSniff\Core;

use PHP_CodeSniffer\Files\File;

class SniffHelper
{
    public static function addViolation(File $file, string $message, int $line, string $sniffCode, string $reportType): void
    {
        if ('error' === $reportType) {
            $file->addErrorOnLine($message, $line, $sniffCode);
        } else {
            $file->addWarningOnLine($message, $line, $sniffCode);
        }
    }
}
