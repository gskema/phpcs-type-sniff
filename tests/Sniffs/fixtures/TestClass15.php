<?php

declare(strict_types=1);

namespace Gskema\TypeSniff\Sniffs\fixtures;

class EmailRequest
{
    public function __construct(
        private readonly Headers $headers = new Headers(['a' => new \stdClass(), 'b' => []]),
        private readonly Headers $headers2 = new Headers([])
    ) {
    }
}

class EmailRequest2
{
    public function __construct(
        private readonly Headers $headers = new Headers,
        private readonly Headers $headers2 = new Headers
    ) {
    }
}
