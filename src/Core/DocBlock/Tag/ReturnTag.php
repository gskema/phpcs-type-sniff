<?php

namespace Gskema\TypeSniff\Core\DocBlock\Tag;

use Gskema\TypeSniff\Core\Type\TypeInterface;

class ReturnTag implements TagInterface
{
    public function __construct(
        protected int $line,
        protected TypeInterface $type,
        protected ?string $description
    ) {
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
