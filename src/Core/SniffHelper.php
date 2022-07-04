<?php

namespace Gskema\TypeSniff\Core;

use PHP_CodeSniffer\Files\File;

class SniffHelper
{
    public static function addViolation(
        File $file,
        string $message,
        int $line,
        string $sniffCode,
        string $reportType,
        ?string $originId,
    ): void {
        if (null !== $originId) {
            $violationId = substr(md5($message . $originId), 0, 16);
            $message = sprintf('%s [%s]', $message, $violationId);
        }

        if ('error' === $reportType) {
            $file->addErrorOnLine($message, $line, $sniffCode);
        } else {
            $file->addWarningOnLine($message, $line, $sniffCode);
        }
    }
}
