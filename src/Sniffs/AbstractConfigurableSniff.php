<?php

namespace Gskema\TypeSniff\Sniffs;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

abstract class AbstractConfigurableSniff implements Sniff
{
    protected bool $configured = false;

    /**
     * @inheritDoc
     */
    public function process(File $file, $ptr): void
    {
        // File path sniffs and not saved using sniff code and their config
        // is not processed. This is a workaround.
        if (!$this->configured) {
            $bits = explode('\\', get_class($this));
            $currentClass = end($bits);

            $opts = [];
            foreach ($file->ruleset->ruleset as $ref => $rule) {
                $bits = explode('/', $ref);
                $cfgClass = rtrim(end($bits), '.php');
                if ($currentClass === $cfgClass) {
                    $opts = $rule['properties'];
                    break;
                }
            }

            // since 3.8.0
            $opts = array_map(fn ($value) => $value['value'] ?? $value, $opts);

            array_walk_recursive($opts, function (&$val) {
                if ('true' === $val) {
                    $val = true;
                } elseif ('false' === $val) {
                    $val = false;
                }
            });

            $this->configure($opts);
            $this->configured = true;
        }

        $this->run($file, $ptr);
    }

    /**
     * @param mixed[] $config
     */
    abstract protected function configure(array $config): void;

    abstract protected function run(File $file, int $ptr): void;
}
