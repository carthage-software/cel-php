<?php

declare(strict_types=1);

namespace Cel\Exception;

/**
 * Thrown when the value contained by an empty optional is dereferenced,
 * e.g. calling `.value()` on `optional.none()`.
 *
 * @api
 */
final class OptionalDereferenceException extends EvaluationException {}
