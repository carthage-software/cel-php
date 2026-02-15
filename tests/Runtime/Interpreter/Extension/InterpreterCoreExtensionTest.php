<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Interpreter\Extension;

use Cel\Runtime\ExecutionBackend;
use Cel\Tests\Runtime\Extension\CoreExtensionTest;
use Override;

final class InterpreterCoreExtensionTest extends CoreExtensionTest
{
    #[Override]
    protected static function getBackend(): ExecutionBackend
    {
        return ExecutionBackend::Interpreter;
    }
}
