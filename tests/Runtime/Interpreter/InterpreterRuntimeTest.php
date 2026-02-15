<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Interpreter;

use Cel\Runtime\ExecutionBackend;
use Cel\Tests\Runtime\RuntimeTest;
use Override;

final class InterpreterRuntimeTest extends RuntimeTest
{
    #[Override]
    protected static function getBackend(): ExecutionBackend
    {
        return ExecutionBackend::Interpreter;
    }
}
