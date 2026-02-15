<?php

declare(strict_types=1);

namespace Cel\VirtualMachine;

use Cel\Span\Span;
use Cel\Value\Value;

/**
 * Readonly bytecode container produced by the Compiler.
 */
final readonly class Program
{
    /**
     * @param list<int<0, max>> $instructions Flat instruction array, 6 ints per instruction [opcode, dst, op1, op2, op3, span_idx].
     * @param list<Value> $constants Constant pool.
     * @param list<string> $strings Interned string pool.
     * @param list<Span> $spans Span pool for error reporting.
     * @param int $registerCount Number of registers needed for execution.
     * @param list<list<string>> $messageFields Field name lists for MAKE_MSG instructions.
     */
    public function __construct(
        public array $instructions,
        public array $constants,
        public array $strings,
        public array $spans,
        public int $registerCount,
        public array $messageFields,
    ) {}
}
