<?php

declare(strict_types=1);

namespace Cel\Runtime\Exception;

use InvalidArgumentException;

final class MisconfigurationException extends InvalidArgumentException implements ExceptionInterface
{
}
