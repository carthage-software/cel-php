<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Interpreter\Extension;

use Cel\Runtime\ExecutionBackend;
use Cel\Tests\Runtime\Extension\DateTimeExtensionTest;
use Override;

final class InterpreterDateTimeExtensionTest extends DateTimeExtensionTest
{
    #[Override]
    protected static function getBackend(): ExecutionBackend
    {
        return ExecutionBackend::Interpreter;
    }
}
