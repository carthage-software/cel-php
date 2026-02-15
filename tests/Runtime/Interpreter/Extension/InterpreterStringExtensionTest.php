<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Interpreter\Extension;

use Cel\Runtime\ExecutionBackend;
use Cel\Tests\Runtime\Extension\StringExtensionTest;
use Override;

final class InterpreterStringExtensionTest extends StringExtensionTest
{
    #[Override]
    protected static function getBackend(): ExecutionBackend
    {
        return ExecutionBackend::Interpreter;
    }
}
