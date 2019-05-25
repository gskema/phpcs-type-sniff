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
    protected $useReflection = false;

    /** @var CodeElementSniffInterface[][] */
    protected $sniffs = [];

    /**
     * @inheritDoc
     */
    protected function configure(array $config): void
    {
        // 1. CompositeCodeElementSniff configuration
        $this->useReflection = $config['useReflection'] ?? false;

        // 2. CodeElementSniff(s) configuration
        // Default sniffs. They can be removed by specifying <property name="FqcnMethodSniff.enabled" value="false"/>
        $config['sniffs'][] = FqcnMethodSniff::class;
        $config['sniffs'][] = FqcnPropSniff::class;
        $config['sniffs'][] = FqcnConstSniff::class;

        // CodeElementSniff(s) are saved by their short name, meaning you can't have 2 instances of same sniff.
        $rawSniffs = [];
        foreach ($config['sniffs'] as $class) {
            $bits = explode('\\', $class);
            $shortClass = end($bits);
            if (!isset($rawSniffs[$shortClass])) {
                $rawSniffs[$shortClass] = ['class' => $class, 'config' => []];
            }
        }

        // Property keys for CodeElementSniff(s) are applied by the short class name.
        // E.g. FqcnMethodSniff.usefulTags
        foreach ($config as $key => $val) {
            if ('sniffs'!== $key && false !== strpos($key, '.')) {
                [$shortClass, $cfgKey] = explode('.', $key, 2);
                if (isset($rawSniffs[$shortClass])) {
                    $rawSniffs[$shortClass]['config'][$cfgKey] = $val;
                }
            }
        }

        foreach ($rawSniffs as $rawSniff) {
            if ($rawSniff['enabled'] ?? true) {
                continue;
            }
            /** @var CodeElementSniffInterface $sniff */
            $sniff = new $rawSniff['class'];
            $sniff->configure($rawSniff['config']);

            $codeElementClasses = $sniff->register();
            foreach ($codeElementClasses as $codeElementClass) {
                $this->sniffs[$codeElementClass][] = $sniff;
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
