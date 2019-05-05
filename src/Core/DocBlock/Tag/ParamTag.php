<?php

namespace Gskema\TypeSniff\Core\DocBlock\Tag;

use Gskema\TypeSniff\Core\Type\TypeInterface;

class ParamTag implements TagInterface
{
    /** @var int */
    protected $line;

    /** @var TypeInterface */
    protected $type;

    /** @var string */
    protected $paramName;

    /** @var string|null */
    protected $description;

    public function __construct(
        int $line,
        TypeInterface $type,
        string $paramName,
        ?string $description
    ) {
        $this->line = $line;
        $this->type = $type;
        $this->paramName = $paramName;
        $this->description = $description;
    }

    /**
     * @inheritDoc
     */
    public function getLine(): int
    {
        return $this->line;
    }

    public function getType(): TypeInterface
    {
        return $this->type;
    }

    public function getParamName(): string
    {
        return $this->paramName;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function hasDescription(): bool
    {
        return !empty($this->description);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'param';
    }
}
