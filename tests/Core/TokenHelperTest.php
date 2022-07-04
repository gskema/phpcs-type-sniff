<?php

namespace Gskema\TypeSniff\Core;

use Gskema\TypeSniff\Core\DocBlock\UndefinedDocBlock;
use PHPUnit\Framework\TestCase;

final class TokenHelperTest extends TestCase
{
    public function testIsClassExtended(): void
    {
        // These test cases not possible (never reached) because if PHP file is invalid
        // code will crash long before these methods are called (e.g. phpcs will crash on invalid PHP class file).
        // These cases can however happen if invalid pointers are passed (code logic error).
        // This test is for coverage only - uses corrupted token lists or invalid pointers.

        $file = MockFile::fromTokenCodes([T_OPEN_TAG, T_CLASS, T_WHITESPACE, T_STRING, T_WHITESPACE]);
        self::assertEquals(false, TokenHelper::isClassExtended($file, 3));

        $file = MockFile::fromTokenCodes([T_WHITESPACE, T_VARIABLE]);
        self::assertEquals(new UndefinedDocBlock(), TokenHelper::getPrevPropDocBlock($file, 1, []));

        $file = MockFile::fromTokenCodes([T_WHITESPACE, T_VARIABLE]);
        self::assertEquals([], TokenHelper::getPrevPropAttributeNames($file, 1));

        $file = MockFile::fromTokenCodes([T_WHITESPACE, T_ATTRIBUTE_END, T_WHITESPACE, T_VARIABLE]);
        self::assertEquals([], TokenHelper::getPrevAttributeNames($file, 3));
    }
}
