<?php

declare(strict_types=1);

namespace Cel\Runtime;

use Cel\Runtime\Value\Value;

final readonly class RuntimeReceipt
{
    public function __construct(
        public Value $result,
        public bool $idempotent,
    ) {}
}
