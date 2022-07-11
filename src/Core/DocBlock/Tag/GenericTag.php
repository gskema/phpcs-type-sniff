<?php

namespace Gskema\TypeSniff\Core\DocBlock\Tag;

class GenericTag implements TagInterface
{
    public function __construct(
        protected int $line,
        protected string $name,
        protected ?string $content
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function getContent(): ?string
    {
        return $this->content;
    }
}
