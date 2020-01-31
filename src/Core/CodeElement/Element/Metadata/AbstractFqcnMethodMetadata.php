<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element\Metadata;

abstract class AbstractFqcnMethodMetadata
{
    /** @var string[]|null */
    protected $nonNullAssignedProps;

    /** @var string|null */
    protected $basicGetterPropName;

    /** @var bool|null */
    protected $extended;

    /**
     * @param string[]|null $nonNullAssignedProps
     * @param string|null   $basicGetterPropName
     * @param bool|null     $extended
     */
    public function __construct(
        ?array $nonNullAssignedProps = null,
        ?string $basicGetterPropName = null,
        ?bool $extended = null
    ) {
        $this->nonNullAssignedProps = $nonNullAssignedProps;
        $this->basicGetterPropName = $basicGetterPropName;
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

    public function isExtended(): ?bool
    {
        return $this->extended;
    }

    public function setExtended(?bool $extended): void
    {
        $this->extended = $extended;
    }
}
