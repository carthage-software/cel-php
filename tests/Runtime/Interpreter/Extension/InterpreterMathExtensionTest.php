<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Interpreter\Extension;

use Cel\Runtime\ExecutionBackend;
use Cel\Tests\Runtime\Extension\MathExtensionTest;
use Override;

final class InterpreterMathExtensionTest extends MathExtensionTest
{
    #[Override]
    protected static function getBackend(): ExecutionBackend
    {
        return ExecutionBackend::Interpreter;
    }
}
