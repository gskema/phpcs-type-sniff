<?php

namespace Gskema\TypeSniff\Sniffs;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

abstract class AbstractConfigurableSniff implements Sniff
{
    /** @var bool */
    protected $configured = false;

    /**
     * @inheritDoc
     */
    public function process(File $file, $ptr): void
    {
        // File path sniffs and not saved using sniff code and their config
        // is not processed. This is a workaround.
        if (!$this->configured) {
            $bits0 = explode('\\', get_class($this));
            $class0 = end($bits0);

            $opts = [];
            foreach ($file->ruleset->ruleset as $ref => $rule) {
                $bits1 = explode('/', $ref);
                $class1 = rtrim(end($bits1), '.php');
                if ($class0 === $class1) {
                    $opts = $rule['properties'];
                    break;
                }
            }

            // @TODO Process arrays
            foreach ($opts as $key => &$val) {
                if ('true' === $val) {
                    $val = true;
                } elseif ('false' === $val) {
                    $val = false;
                }
            }
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
