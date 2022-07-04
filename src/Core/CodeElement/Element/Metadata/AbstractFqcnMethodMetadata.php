<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element\Metadata;

abstract class AbstractFqcnMethodMetadata
{
    /** @var string[]|null */
    protected ?array $nonNullAssignedProps;

    protected ?string $basicGetterPropName;

    /** @var string[]|null */
    protected ?array $thisMethodCalls;

    protected ?bool $extended;

    /**
     * @param string[]|null $nonNullAssignedProps
     * @param string|null   $basicGetterPropName
     * @param string[]|null $thisMethodCalls
     * @param bool|null     $extended
     */
    public function __construct(
        ?array $nonNullAssignedProps = null,
        ?string $basicGetterPropName = null,
        ?array $thisMethodCalls = null,
        ?bool $extended = null,
    ) {
        $this->nonNullAssignedProps = $nonNullAssignedProps;
        $this->basicGetterPropName = $basicGetterPropName;
        $this->thisMethodCalls = $thisMethodCalls;
        $this->extended = $extended;
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
