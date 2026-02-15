<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Interpreter\Extension;

use Cel\Runtime\ExecutionBackend;
use Cel\Tests\Runtime\Extension\ListExtensionTest;
use Override;

final class InterpreterListExtensionTest extends ListExtensionTest
{
    #[Override]
    protected static function getBackend(): ExecutionBackend
    {
        return ExecutionBackend::Interpreter;
    }
}
