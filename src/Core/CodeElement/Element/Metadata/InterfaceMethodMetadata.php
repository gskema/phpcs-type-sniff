<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element\Metadata;

class InterfaceMethodMetadata
{
    public function __construct(
        protected ?bool $extended = null
    ) {
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
