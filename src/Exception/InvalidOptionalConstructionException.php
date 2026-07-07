<?php

declare(strict_types=1);

namespace Cel\Exception;

/**
 * Thrown when an optional map entry, message field, or list element
 * (`{?key: v}`, `Msg{?field: v}`, `[?v]`) is given a value that is not an
 * `optional`.
 *
 * @api
 */
final class InvalidOptionalConstructionException extends EvaluationException {}
