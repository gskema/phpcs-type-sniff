<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element\Metadata;

abstract class AbstractFqcnPropMetadata
{
    public function __construct(
        protected ?bool $hasDefaultValue = null,
    ) {
    }

    public function hasDefaultValue(): ?bool
    {
        return $this->hasDefaultValue;
    }

    public function setHasDefaultValue(?bool $hasDefaultValue): void
    {
        $this->hasDefaultValue = $hasDefaultValue;
    }
}
