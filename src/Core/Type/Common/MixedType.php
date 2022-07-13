<?php

namespace Gskema\TypeSniff\Core\Type\Common;

use Gskema\TypeSniff\Core\Type\TypeInterface;

/**
 * @see https://php.watch/versions/8.0/mixed-type
 */
class MixedType implements TypeInterface
{
    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return 'mixed';
    }
}
