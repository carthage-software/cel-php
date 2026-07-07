<?php

declare(strict_types=1);

namespace Cel\Exception;

/**
 * Thrown when a message cannot be constructed due to invalid or missing fields.
 *
 * @api
 */
final class MessageConstructionException extends EvaluationException {}
