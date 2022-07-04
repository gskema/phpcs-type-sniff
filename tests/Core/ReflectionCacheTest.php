<?php

namespace Gskema\TypeSniff\Core;

use Gskema\TypeSniff\Core\FixtureClass\TestClass13;
use PHPUnit\Framework\TestCase;

final class ReflectionCacheTest extends TestCase
{
    public function testGetPropsRecursive(): void
    {
        $actualProps = ReflectionCache::getPropsRecursive(TestClass13::class, true);

        self::assertEquals([], $actualProps);
    }
}
