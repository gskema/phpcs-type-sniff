<?php

namespace Gskema\TypeSniff\Sniffs\CodeElement;

use Gskema\TypeSniff\Core\CodeElement\Element\ClassElement;
use Gskema\TypeSniff\Core\CodeElement\Element\CodeElementInterface;
use Gskema\TypeSniff\Core\CodeElement\Element\InterfaceElement;
use Gskema\TypeSniff\Core\CodeElement\Element\TraitElement;
use PHP_CodeSniffer\Files\File;

class FqcnDescriptionSniff implements CodeElementSniffInterface
{
    protected const CODE = 'FqcnDescriptionSniff';

    /** @var string[] */
    protected $invalidPatterns = [
        '^(Class|Trait|Interface)\s+\w+\s*$',
    ];

    /** @var string[] */
    protected $invalidTags = [
        '@package'
    ];

    /**
     * @inheritDoc
     */
    public function configure(array $config): void
    {
        $this->invalidPatterns = array_merge($this->invalidPatterns, $config['invalidPatterns'] ?? []);
        foreach ($this->invalidPatterns as &$invalidPattern) {
            $invalidPattern = '#'.$invalidPattern.'#i';
        }

        $this->invalidTags = array_merge($this->invalidTags, $config['invalidTags'] ?? []);
        foreach ($this->invalidTags as &$invalidTag) {
             $invalidTag = substr($invalidTag, 1);
        }
    }

    /**
     * @inheritDoc
     */
    public function register(): array
    {
        return [
            ClassElement::class,
            InterfaceElement::class,
            TraitElement::class,
        ];
    }

    /**
     * @inheritDoc
     */
    public function process(File $file, CodeElementInterface $element, CodeElementInterface $parentElement): void
    {
        foreach ($element->getDocBlock()->getDescriptionLines() as $lineNum => $descriptionLine) {
            foreach ($this->invalidPatterns as $invalidPattern) {
                if (preg_match($invalidPattern, $descriptionLine)) {
                    $file->addWarningOnLine('Useless description', $lineNum, static::CODE);
                }
            }
        }

        foreach ($element->getDocBlock()->getTags() as $tag) {
            foreach ($this->invalidTags as $invalidTagName) {
                if ($tag->getName() === $invalidTagName) {
                    $file->addWarningOnLine('Useless tag', $tag->getLine(), static::CODE);
                }
            }
        }
    }
}
