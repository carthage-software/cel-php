<?php

declare(strict_types=1);

namespace Cel\Exception;

use DomainException;

/**
 * Thrown when an unsupported PHP type is encountered during value conversion.
 */
final class IncompatibleValueTypeException extends DomainException implements ExceptionInterface {}
