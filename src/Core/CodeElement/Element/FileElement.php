<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\DocBlock\DocBlock;

class FileElement implements CodeElementInterface
{
    /** @var int */
    protected $line;

    /** @var DocBlock */
    protected $docBlock;

    /** @var string */
    protected $path;

    /** @var ConstElement[] */
    protected $constants = [];

    /** @var FunctionElement[] */
    protected $functions = [];

    /** @var ClassElement[] */
    protected $classes = [];

    /** @var TraitElement[] */
    protected $traits = [];

    /** @var InterfaceElement[] */
    protected $interfaces = [];

    public function __construct(int $line, DocBlock $docBlock, string $path)
    {
        $this->line = $line;
        $this->docBlock = $docBlock;
        $this->path = $path;
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
}
