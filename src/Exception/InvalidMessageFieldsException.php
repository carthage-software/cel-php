<?php

declare(strict_types=1);

namespace Cel\Exception;

use InvalidArgumentException;

/**
 * @api
 */
final class InvalidMessageFieldsException extends InvalidArgumentException implements ExceptionInterface {}
