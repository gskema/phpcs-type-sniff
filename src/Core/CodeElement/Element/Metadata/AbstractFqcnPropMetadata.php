<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element\Metadata;

abstract class AbstractFqcnPropMetadata
{
    /** @var bool|null */
    protected $hasDefaultValue;

    public function __construct(?bool $hasDefaultValue = null)
    {
        $this->hasDefaultValue = $hasDefaultValue;
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
