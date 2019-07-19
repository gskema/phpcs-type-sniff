<?php

namespace Gskema\TypeSniff\Core\Func;

use Gskema\TypeSniff\Core\Type\TypeInterface;

/**
 * @see FunctionParamTest
 */
class FunctionParam
{
    /** @var int */
    protected $line;

    /** @var string */
    protected $name;

    /** @var TypeInterface */
    protected $type;

    /** @var TypeInterface|null */
    protected $valueType;

    public function __construct(
        int $line,
        string $name,
        TypeInterface $declarationType,
        ?TypeInterface $valueType
    ) {
        $this->line = $line;
        $this->name = $name;
        $this->type = $declarationType;
        $this->valueType = $valueType;
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

    public function getValueType(): ?TypeInterface
    {
        return $this->valueType;
    }
}
