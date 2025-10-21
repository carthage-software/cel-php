<?php

declare(strict_types=1);

namespace Cel\Exception;

/**
 * Thrown when accessing a map key or message field that does not exist.
 */
final class NoSuchKeyException extends EvaluationException
{
}
