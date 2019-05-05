<?php

namespace Gskema\TypeSniff\Core\CodeElement\Element;

use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\Func\FunctionSignature;

abstract class AbstractFqcnMethodElement extends AbstractFqcnElement
{
    /** @var FunctionSignature */
    protected $signature;

    /** @var bool|null */
    protected $extended; // null when not detected

    public function __construct(DocBlock $docBlock, string $fqcn, FunctionSignature $signature, ?bool $extended)
    {
        parent::__construct($signature->getLine(), $docBlock, $fqcn);
        $this->signature = $signature;
        $this->extended = $extended;
    }

    public function getSignature(): FunctionSignature
    {
        return $this->signature;
    }

    public function isExtended(): ?bool
    {
        return $this->extended;
    }
}
