<?php

namespace Gskema\TypeSniff\Sniffs;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use Gskema\TypeSniff\Core\CodeElement\CodeElementDetector;
use Gskema\TypeSniff\Sniffs\CodeElement\CodeElementSniffInterface;
use Gskema\TypeSniff\Sniffs\CodeElement\FqcnConstSniff;
use Gskema\TypeSniff\Sniffs\CodeElement\FqcnMethodSniff;
use Gskema\TypeSniff\Sniffs\CodeElement\FqcnPropSniff;

class CompositeCodeElementSniff implements Sniff
{
    /** @var CodeElementSniffInterface[][] */
    protected $sniffs = [];

    public function __construct()
    {
        /** @var CodeElementSniffInterface[] $sniffs */
        $sniffs = [
            new FqcnMethodSniff(),
            new FqcnPropSniff(),
            new FqcnConstSniff(),
        ];

        foreach ($sniffs as $sniff) {
            $classNames = $sniff->register();
            foreach ($classNames as $className) {
                $this->sniffs[$className][] = $sniff;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function register()
    {
        return [
            T_OPEN_TAG,
        ];
    }

    /**
     * @inheritDoc
     */
    public function process(File $file, $openTagPtr)
    {
        $elements = CodeElementDetector::detectFromTokens($file, true);

        foreach ($elements as $element) {
            $className = get_class($element);
            foreach ($this->sniffs[$className] ?? [] as $sniff) {
                $sniff->process($file, $element);
            }
        }
    }
}
