<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\DocBlock\DocBlock;

class FileElement implements CodeElementInterface
{
    public function __construct(
        protected int $line,
        protected DocBlock $docBlock,
        protected string $path,
        /** @var ConstElement[] */
        protected array $constants = [],
        /** @var FunctionElement[] */
        protected array $functions = [],
        /** @var ClassElement[] */
        protected array $classes = [],
        /** @var TraitElement[] */
        protected array $traits = [],
        /** @var InterfaceElement[] */
        protected array $interfaces = [],
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
    public function getDocBlock(): DocBlock
    {
        return $this->docBlock;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return ConstElement[]
     */
    public function getConstants(): array
    {
        return $this->constants;
    }

    /**
     * @return FunctionElement[]
     */
    public function getFunctions(): array
    {
        return $this->functions;
    }

    /**
     * @return ClassElement[]
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    /**
     * @return TraitElement[]
     */
    public function getTraits(): array
    {
        return $this->traits;
    }

    /**
     * @return InterfaceElement[]
     */
    public function getInterfaces(): array
    {
        return $this->interfaces;
    }

    public function addConstant(ConstElement $element): void
    {
        $this->constants[] = $element;
    }

    public function addFunction(FunctionElement $element): void
    {
        $this->functions[] = $element;
    }

    public function addClass(ClassElement $element): void
    {
        $this->classes[] = $element;
    }

    public function addTrait(TraitElement $element): void
    {
        $this->traits[] = $element;
    }

    public function addInterface(InterfaceElement $element): void
    {
        $this->interfaces[] = $element;
    }

    /**
     * @inheritDoc
     */
    public function getAttributeNames(): array
    {
        return []; // files do not have attributes
    }
}
