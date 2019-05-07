<?php

namespace Gskema\TypeSniff\Core\DocBlock\Tag;

use Gskema\TypeSniff\Core\Type\TypeInterface;

class ReturnTag implements TagInterface
{
    /** @var int */
    protected $line;

    /** @var TypeInterface */
    protected $type;

    /** @var string */
    protected $description;

    public function __construct(int $line, TypeInterface $type, ?string $description)
    {
        $this->line = $line;
        $this->type = $type;
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

    public function getDescription(): ?string
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
        return 'return';
    }
}
