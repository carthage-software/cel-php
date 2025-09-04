<?php

declare(strict_types=1);

namespace Cel;

use function strtolower;

final readonly class Example
{
    public function greet(string $name): string
    {
        return 'Hello, ' . strtolower($name) . '!';
    }
}
