<?php

namespace Gskema\TypeSniff\Core\Type\Common;

use Gskema\TypeSniff\Core\Type\TypeInterface;

/**
 * @see https://www.php.net/manual/en/language.types.never.php
 */
class NeverType implements TypeInterface
{
    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return 'never';
    }
}
