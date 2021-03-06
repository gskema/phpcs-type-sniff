<?php

namespace Gskema\TypeSniff\Core\CodeElement\fixtures;

use PHP_CodeSniffer\Util\Tokens;

function namedFunc()
{
}

const CONST1 = 2;

if (1) {
    class TestClass1
    {
        public function method1()
        {
            $dynamic = 'name';
            $args = [1, 2];

            $a = $this->{$dynamic}(...$args);

            $cb = function () {
                $a = 11;
            };
            try {

            } catch (\Throwable $e) {
                $a = 1;
            } finally {
                //
                $ab = 1;
            }

            switch (1) {
                case 3:
                    {
                        $a = 1;
                    }
                default:
                    {
                        $v = 1;
                    }
            }

            Tokens::$scopeOpeners;

            $anon1 = new class implements \JsonSerializable
            {
                protected $a = 1;
            };

            $this->method1();
        }

        /** @var int */
        protected $prop1;

        public const CONST2 = 1;
    }
}
