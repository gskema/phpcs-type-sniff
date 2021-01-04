<?php

namespace Gskema\TypeSniff\Inspection\Subject;

use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\DocBlock\UndefinedDocBlock;
use Gskema\TypeSniff\Core\SniffHelper;
use Gskema\TypeSniff\Core\Type\Common\UndefinedType;
use Gskema\TypeSniff\Core\Type\TypeInterface;
use PHP_CodeSniffer\Files\File;

/**
 * @see AbstractTypeSubjectTest
 */
abstract class AbstractTypeSubject
{
    /** @var TypeInterface|null */
    protected $docType; // null = missing in PHPDoc

    /** @var TypeInterface */
    protected $fnType;

    /** @var TypeInterface|null */
    protected $valueType; // null = could not be detected

    /** @var int|null */
    protected $docTypeLine; // null = missing in PHPDoc

    /** @var int */
    protected $fnTypeLine;

    /** @var string */
    protected $name; // "parameter $param1", "property $prop1", "constant CONST1"

    /** @var DocBlock */
    protected $docBlock;

    /** @var string[] */
    protected $docTypeWarnings = [];

    /** @var string[] */
    protected $fnTypeWarnings = [];

    public function __construct(
        ?TypeInterface $docType,
        TypeInterface $fnType,
        ?TypeInterface $valueType,
        ?int $docTypeLine,
        int $fnTypeLine,
        string $name,
        DocBlock $docBlock
    ) {
        $this->docType = $docType;
        $this->fnType = $fnType;
        $this->valueType = $valueType;
        $this->docTypeLine = $docTypeLine;
        $this->fnTypeLine = $fnTypeLine;
        $this->name = $name;
        $this->docBlock = $docBlock;
    }

    public function getDocType(): ?TypeInterface
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

    public function getDocTypeLine(): ?int
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

    public function getDocBlock(): DocBlock
    {
        return $this->docBlock;
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

    public function hasDefinedDocBlock(): bool
    {
        return !($this->docBlock instanceof UndefinedDocBlock);
    }

    public function addDocTypeWarning(string $warning): void
    {
        $this->docTypeWarnings[] = $warning;
    }

    public function addFnTypeWarning(string $warning): void
    {
        $this->fnTypeWarnings[] = $warning;
    }

    /**
     * @deprecated Use ::writeViolationsTo()
     * @param File   $file
     * @param string $sniffCode
     */
    public function writeWarningsTo(File $file, string $sniffCode): void
    {
        $this->writeViolationsTo($file, $sniffCode, 'warning');
    }

    public function writeViolationsTo(File $file, string $sniffCode, string $reportType): void
    {
        $ucName = ucfirst($this->name);
        foreach ($this->docTypeWarnings as $docTypeWarning) {
            $warning = str_replace([':subject:', ':Subject:'], [$this->name, $ucName], $docTypeWarning);
            SniffHelper::addViolation($file, $warning, $this->docTypeLine ?? $this->fnTypeLine, $sniffCode, $reportType);
        }

        foreach ($this->fnTypeWarnings as $fnTypeWarning) {
            $warning = str_replace([':subject:', ':Subject:'], [$this->name, $ucName], $fnTypeWarning);
            SniffHelper::addViolation($file, $warning, $this->fnTypeLine, $sniffCode, $reportType);
        }
    }

    public function hasDocTypeTag(): bool
    {
        return null !== $this->docType;
    }
}
