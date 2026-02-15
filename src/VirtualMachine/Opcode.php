<?php

declare(strict_types=1);

namespace Cel\VirtualMachine;

final readonly class Opcode
{
    public const int LOAD_CONST = 0;
    public const int LOAD_VAR = 1;
    public const int LOAD_TRUE = 2;
    public const int LOAD_FALSE = 3;
    public const int LOAD_NULL = 4;
    public const int NEGATE = 5;
    public const int NOT = 6;
    public const int BINARY_OP = 7;
    public const int JUMP_IF_FALSE = 8;
    public const int JUMP_IF_TRUE = 9;
    public const int JUMP = 10;
    public const int COND_CHECK = 11;
    public const int MEMBER_ACCESS = 12;
    public const int INDEX = 13;
    public const int CALL = 14;
    public const int MAKE_LIST = 15;
    public const int MAKE_MAP = 16;
    public const int MAKE_MSG = 17;
    public const int HAS_FIELD = 18;
    public const int ITER_INIT = 19;
    public const int ITER_NEXT = 20;
    public const int SCOPE_PUSH = 21;
    public const int SCOPE_POP = 22;
    public const int BIND_VAR = 23;
    public const int LIST_APPEND = 24;
    public const int INT_INC = 25;
    public const int MOVE = 26;
    public const int RETURN = 27;

    private function __construct() {}
}
