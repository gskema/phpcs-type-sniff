<?php

namespace Gskema\TypeSniff\Sniffs\fixtures;

class TestClass13
{
    /**
     * @return $this
     */
    public function create0()
    {
    }

    public function create1(): self
    {
    }

    public function create2(): static
    {
    }

    /**
     * @return $this
     */
    public function create3(): self
    {
    }

    /**
     * @return $this
     */
    public function create4(): static
    {
    }

    /**
     * @return self
     */
    public function create5()
    {
    }

    /**
     * @return self
     */
    public function create6(): self
    {
    }

    /**
     * @return self
     */
    public function create7(): static
    {
    }

    /**
     * @return static
     */
    public function create8()
    {
    }

    /**
     * @return static
     */
    public function create9(): self
    {
    }

    /**
     * @return static
     */
    public function create10(): static
    {
    }
}
