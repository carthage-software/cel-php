<?php

declare(strict_types=1);

namespace Cel\Exception;

use LogicException;

/**
 * @api
 */
final class ConflictingFunctionSignatureException extends LogicException implements ExceptionInterface {}
