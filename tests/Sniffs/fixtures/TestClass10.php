<?php

namespace Gskema\TypeSniff\Sniffs\fixtures;

use JetBrains\PhpStorm\ArrayShape;

class TestClass10
{
    public $prop1;

    public $prop2 = null;

    public $prop3 = 1;

    /** @var string */
    public $prop4;

    /** @var string|null */
    public $prop5;

    /** @var string|int|null */
    public $prop6;

    /** @var string|int|null */
    public ?string $prop7;

    /** @var string|int */
    public ?string $prop8;

    /** @var string|null */
    public ?string $prop9;

    /** @var string[] */
    public array $prop10;

    #[ArrayShape(['key' => 'int'])]
    public array $prop11;

    /**
     * @Assert\Length(max="255")
     * @Assert\NotBlank()
     */
    private string $prop12;

    /**
     * @Assert\Range(min="1", max="5")
     * @var int
     */
    private int $prop13;
}
