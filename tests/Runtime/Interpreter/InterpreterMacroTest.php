<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Interpreter;

use Cel\Runtime\ExecutionBackend;
use Cel\Tests\Runtime\MacroTest;
use Override;

final class InterpreterMacroTest extends MacroTest
{
    #[Override]
    protected static function getBackend(): ExecutionBackend
    {
        return ExecutionBackend::Interpreter;
    }
}
