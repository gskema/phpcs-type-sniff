<?php

namespace Gskema\TypeSniff\Core\DocBlock\Tag;

class GenericTag implements TagInterface
{
    protected int $line;

    protected string $name;

    protected ?string $content;

    public function __construct(int $line, string $name, ?string $content)
    {
        $this->line = $line;
        $this->name = $name;
        $this->content = $content;
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
