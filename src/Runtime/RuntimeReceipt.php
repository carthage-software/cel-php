<?php

declare(strict_types=1);

namespace Cel\Runtime;

use Cel\Value\Value;

/**
 * @api
 */
final readonly class RuntimeReceipt
{
    public function __construct(
        public Value $result,
        public bool $idempotent,
    ) {}
}
