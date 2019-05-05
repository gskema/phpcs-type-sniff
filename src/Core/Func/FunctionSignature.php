<?php

namespace Gskema\TypeSniff\Core\Func;

use Gskema\TypeSniff\Core\Type\TypeInterface;

class FunctionSignature
{
    /** @var int */
    protected $line;

    /** @var string */
    protected $name;

    /** @var FunctionParam[] */
    protected $params = [];

    /** @var TypeInterface */
    protected $returnType;

    /** @var int */
    protected $returnLine;

    /**
     * @param int             $line
     * @param string          $name
     * @param FunctionParam[] $params
     * @param TypeInterface   $returnType
     * @param int             $returnLine
     */
    public function __construct(
        int $line,
        string $name,
        array $params,
        TypeInterface $returnType,
        int $returnLine
    ) {
        $this->line = $line;
        $this->name = $name;
        $this->params = $params;
        $this->returnType = $returnType;
        $this->returnLine = $returnLine;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return FunctionParam[]
     */
    public function getParams(): array
    {
        return $this->params;
    }

    public function getReturnType(): TypeInterface
    {
        return $this->returnType;
    }

    public function getReturnLine(): int
    {
        return $this->returnLine;
    }

    public function hasParam(string $name): bool
    {
        return key_exists($name, $this->params);
    }

    public function getParam(string $name): ?FunctionParam
    {
        return $this->params[$name] ?? null;
    }
}
