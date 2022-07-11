<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element\Metadata;

abstract class AbstractFqcnMethodMetadata
{
    public function __construct(
        /** @var string[]|null */
        protected ?array $nonNullAssignedProps = null,
        protected ?string $basicGetterPropName = null,
        /** @var string[]|null */
        protected ?array $thisMethodCalls = null,
        protected ?bool $extended = null,
    ) {
    }

    /**
     * @return string[]|null
     */
    public function getNonNullAssignedProps(): ?array
    {
        return $this->nonNullAssignedProps;
    }

    /**
     * @param string[]|null $nonNullAssignedProps
     */
    public function setNonNullAssignedProps(?array $nonNullAssignedProps): void
    {
        $this->nonNullAssignedProps = $nonNullAssignedProps;
    }

    public function getBasicGetterPropName(): ?string
    {
        return $this->basicGetterPropName;
    }

    public function setBasicGetterPropName(?string $basicGetterPropName): void
    {
        $this->basicGetterPropName = $basicGetterPropName;
    }

    public function isBasicGetter(): bool
    {
        return null !== $this->basicGetterPropName;
    }

    /**
     * @return string[]|null
     */
    public function getThisMethodCalls(): ?array
    {
        return $this->thisMethodCalls;
    }

    /**
     * @param string[]|null $thisMethodCalls
     */
    public function setThisMethodCalls(?array $thisMethodCalls): void
    {
        $this->thisMethodCalls = $thisMethodCalls;
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
