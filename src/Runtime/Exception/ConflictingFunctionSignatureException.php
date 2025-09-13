<?php

declare(strict_types=1);

namespace Cel\Runtime\Exception;

use LogicException;

final class ConflictingFunctionSignatureException extends LogicException implements ExceptionInterface
{
}
