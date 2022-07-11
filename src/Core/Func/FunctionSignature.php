<?php

namespace Gskema\TypeSniff\Core\Func;

use Gskema\TypeSniff\Core\Type\TypeInterface;

/**
 * @see FunctionSignatureTest
 */
class FunctionSignature
{
    public function __construct(
        protected int $line,
        protected string $name,
        /** @var FunctionParam[] */
        protected array $params,
        protected TypeInterface $returnType,
        protected int $returnLine,
    ) {
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
        return null !== $this->getParam($name);
    }

    public function getParam(string $name): ?FunctionParam
    {
        foreach ($this->params as $param) {
            if ($param->getName() === $name) {
                return $param;
            }
        }

        return null;
    }
}
