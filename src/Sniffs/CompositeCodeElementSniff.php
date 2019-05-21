<?php

namespace Gskema\TypeSniff\Sniffs;

use PHP_CodeSniffer\Files\File;
use Gskema\TypeSniff\Core\CodeElement\CodeElementDetector;
use Gskema\TypeSniff\Sniffs\CodeElement\CodeElementSniffInterface;
use Gskema\TypeSniff\Sniffs\CodeElement\FqcnConstSniff;
use Gskema\TypeSniff\Sniffs\CodeElement\FqcnMethodSniff;
use Gskema\TypeSniff\Sniffs\CodeElement\FqcnPropSniff;

class CompositeCodeElementSniff extends AbstractConfigurableSniff
{
    /** @var bool */
    public $useReflection = false;

    /** @var CodeElementSniffInterface[][] */
    protected $sniffs = [];

    /**
     * @inheritDoc
     */
    protected function configure(array $config): void
    {
        $this->useReflection = $config['useReflection'] ?? false;

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
    protected function run(File $file, int $openTagPtr): void
    {
        $elements = CodeElementDetector::detectFromTokens($file, $this->useReflection);

        foreach ($elements as $element) {
            $className = get_class($element);
            foreach ($this->sniffs[$className] ?? [] as $sniff) {
                $sniff->process($file, $element);
            }
        }
    }
}
