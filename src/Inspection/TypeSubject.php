<?php

namespace Gskema\TypeSniff\Inspection;

use Gskema\TypeSniff\Core\Type\Common\UndefinedType;
use Gskema\TypeSniff\Core\Type\TypeInterface;
use PHP_CodeSniffer\Files\File;

class TypeSubject
{
    /** @var TypeInterface */
    protected $docType; // null = missing in PHPDoc

    /** @var TypeInterface */
    protected $fnType;

    /** @var TypeInterface|null */
    protected $valueType; // null = could not be detected

    /** @var int */
    protected $docTypeLine;

    /** @var int */
    protected $fnTypeLine;

    /** @var string */
    protected $name; // "parameter $param1", "property $prop1", "constant CONST1"

    /** @var bool */
    protected $returnType;

    /** @var string[] */
    protected $docTypeWarnings = [];

    /** @var string[] */
    protected $fnTypeWarnings = [];

    public function __construct(
        TypeInterface $docType,
        TypeInterface $fnType,
        ?TypeInterface $valueType,
        int $docTypeLine,
        int $declarationTypeLine,
        string $name,
        bool $returnType
    ) {
        $this->docType = $docType;
        $this->fnType = $fnType;
        $this->valueType = $valueType;
        $this->docTypeLine = $docTypeLine;
        $this->fnTypeLine = $declarationTypeLine;
        $this->name = $name;
        $this->returnType = $returnType;
    }

    public function getDocType(): TypeInterface
    {
        return $this->docType;
    }

    public function getFnType(): TypeInterface
    {
        return $this->fnType;
    }

    public function getValueType(): ?TypeInterface
    {
        return $this->valueType;
    }

    public function getDocTypeLine(): int
    {
        return $this->docTypeLine;
    }

    public function getFnTypeLine(): int
    {
        return $this->fnTypeLine;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isReturnType(): bool
    {
        return $this->returnType;
    }

    /**
     * @return string[]
     */
    public function getDocTypeWarnings(): array
    {
        return $this->docTypeWarnings;
    }

    /**
     * @return string[]
     */
    public function getFnTypeWarnings(): array
    {
        return $this->fnTypeWarnings;
    }

    public function hasDefinedDocType(): bool
    {
        return $this->docType && !($this->docType instanceof UndefinedType);
    }

    public function hasDefinedFnType(): bool
    {
        return $this->fnType && !($this->fnType instanceof UndefinedType);
    }

    public function addDocTypeWarning(string $warning): void
    {
        $this->docTypeWarnings[] = $warning;
    }

    public function addFnTypeWarning(string $warning): void
    {
        $this->fnTypeWarnings[] = $warning;
    }

    public function passWarningsTo(File $file, string $sniffCode): void
    {
        foreach ($this->docTypeWarnings as $docTypeWarning) {
            $warning = str_replace(':subject:', $this->name, $docTypeWarning);
            $file->addWarningOnLine($warning, $this->docTypeLine, $sniffCode);
        }

        foreach ($this->fnTypeWarnings as $fnTypeWarning) {
            $warning = str_replace(':subject:', $this->name, $fnTypeWarning);
            $file->addWarningOnLine($warning, $this->fnTypeLine, $sniffCode);
        }
    }
}
