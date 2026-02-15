<?php

declare(strict_types=1);

namespace Cel\Runtime;

enum ExecutionBackend
{
    case Interpreter;
    case VirtualMachine;
}
