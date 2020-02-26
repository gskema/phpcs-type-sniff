<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element\Metadata;

class InterfaceMethodMetadata
{
    /** @var bool|null */
    protected $extended;

    public function __construct(?bool $extended = null)
    {
        $this->extended = $extended;
    }

    public function isExtended(): ?bool
    {
        return $this->extended;
    }

    public function setExtended(?bool $extended): void
    {
        $this->extended = $extended;
    }
}
