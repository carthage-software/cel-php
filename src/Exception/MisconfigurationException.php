<?php

declare(strict_types=1);

namespace Cel\Exception;

use InvalidArgumentException;

final class MisconfigurationException extends InvalidArgumentException implements ExceptionInterface
{
    public static function forMessage(string $message): self
    {
        return new self($message);
    }
}
