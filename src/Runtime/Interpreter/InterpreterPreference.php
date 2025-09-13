<?php

declare(strict_types=1);

namespace Cel\Runtime\Interpreter;

/**
 * @TODO(azjezz): Add a stack-based VM preference.
 */
enum InterpreterPreference
{
    case TreeWalking;
}
