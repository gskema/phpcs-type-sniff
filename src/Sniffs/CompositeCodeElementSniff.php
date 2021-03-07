<?php

namespace Gskema\TypeSniff\Sniffs;

use Generator;
use Gskema\TypeSniff\Core\CodeElement\Element\CodeElementInterface;
use Gskema\TypeSniff\Core\CodeElement\Element\FileElement;
use Gskema\TypeSniff\Sniffs\CodeElement\FqcnDescriptionSniff;
use PHP_CodeSniffer\Files\File;
use Gskema\TypeSniff\Core\CodeElement\CodeElementDetector;
use Gskema\TypeSniff\Sniffs\CodeElement\CodeElementSniffInterface;
use Gskema\TypeSniff\Sniffs\CodeElement\FqcnConstSniff;
use Gskema\TypeSniff\Sniffs\CodeElement\FqcnMethodSniff;
use Gskema\TypeSniff\Sniffs\CodeElement\FqcnPropSniff;

/**
 * @see CompositeCodeElementSniffTest
 */
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
        // 0. Global config
        $globalReportType = $config['reportType'] ?? null;
        $globalAddViolationId = $config['addViolationId'] ?? false;

        // 1. CompositeCodeElementSniff configuration
        $this->useReflection = $config['useReflection'] ?? false;

        // 2. CodeElementSniff(s) configuration
        // Default sniffs. They can be removed by specifying <property name="FqcnMethodSniff.enabled" value="false"/>
        $config['sniffs'][] = FqcnMethodSniff::class;
        $config['sniffs'][] = FqcnPropSniff::class;
        $config['sniffs'][] = FqcnConstSniff::class;
        $config['sniffs'][] = FqcnDescriptionSniff::class;

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
        // E.g. FqcnMethodSniff.invalidTags
        foreach ($config as $key => $val) {
            if ('sniffs' !== $key && false !== strpos($key, '.')) {
                [$shortClass, $cfgKey] = explode('.', $key, 2);
                if (isset($rawSniffs[$shortClass])) {
                    $rawSniffs[$shortClass]['config'][$cfgKey] = $val;
                }
            }
        }

        foreach ($rawSniffs as $rawSniff) {
            $enabled = $rawSniff['config']['enabled'] ?? true;
            if (!$enabled) {
                continue;
            }

            // Modify individual sniff configs with global config values
            $rawSniff['config']['reportType'] = $rawSniff['config']['reportType'] ?? $globalReportType ?? null;
            $rawSniff['config']['addViolationId'] = $globalAddViolationId;

            /** @var CodeElementSniffInterface $sniff */
            $sniff = new $rawSniff['class']();
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
        $fileElement = CodeElementDetector::detectFromTokens($file, $this->useReflection);

        foreach ($this->getArgIterator($fileElement) as [$element, $parentElement]) {
            $className = get_class($element);
            foreach ($this->sniffs[$className] ?? [] as $sniff) {
                $sniff->process($file, $element, $parentElement);
            }
        }
    }

    /**
     * @param FileElement $file
     *
     * @return Generator|CodeElementInterface[][]
     */
    protected function getArgIterator(FileElement $file): Generator
    {
        // world's most complicated iterator
        yield [$file, $file];
        foreach ($file->getConstants() as $constant) {
            yield [$constant, $file];
        }
        foreach ($file->getFunctions() as $function) {
            yield [$function, $file];
        }
        foreach ($file->getClasses() as $class) {
            yield [$class, $file];
            foreach ($class->getConstants() as $constant) {
                yield [$constant, $class];
            }
            foreach ($class->getProperties() as $prop) {
                yield [$prop, $class];
            }
            foreach ($class->getMethods() as $method) {
                yield [$method, $class];
            }
        }
        foreach ($file->getTraits() as $trait) {
            yield [$trait, $file];
            foreach ($trait->getProperties() as $prop) {
                yield [$prop, $trait];
            }
            foreach ($trait->getMethods() as $method) {
                yield [$method, $trait];
            }
        }
        foreach ($file->getInterfaces() as $interface) {
            yield [$interface, $file];
            foreach ($interface->getConstants() as $constant) {
                yield [$constant, $interface];
            }
            foreach ($interface->getMethods() as $method) {
                yield [$method, $interface];
            }
        }
    }
}
