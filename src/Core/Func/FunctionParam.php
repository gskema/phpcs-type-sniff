<?php

namespace Gskema\TypeSniff\Core\Func;

use Gskema\TypeSniff\Core\Type\TypeInterface;

class FunctionParam
{
    /** @var int */
    protected $line;

    /** @var string */
    protected $name;

    /** @var TypeInterface */
    protected $type;

    public function __construct(int $line, string $name, TypeInterface $type)
    {
        $this->line = $line;
        $this->name = $name;
        $this->type = $type;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): TypeInterface
    {
        return $this->type;
    }
}
