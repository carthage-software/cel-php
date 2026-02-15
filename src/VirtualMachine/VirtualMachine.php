<?php

declare(strict_types=1);

namespace Cel\VirtualMachine;

use Cel\Environment\EnvironmentInterface;
use Cel\Exception\EvaluationException;
use Cel\Exception\InvalidConditionTypeException;
use Cel\Exception\InvalidMacroCallException;
use Cel\Exception\MessageConstructionException;
use Cel\Exception\NoSuchFunctionException;
use Cel\Exception\NoSuchKeyException;
use Cel\Exception\NoSuchOverloadException;
use Cel\Exception\NoSuchTypeException;
use Cel\Exception\NoSuchVariableException;
use Cel\Exception\UnexpectedMapKeyTypeException;
use Cel\Exception\UnsupportedOperationException;
use Cel\Message\MessageInterface;
use Cel\Operator\BinaryOperatorOverloadHandlerInterface;
use Cel\Runtime\Configuration;
use Cel\Runtime\OperationRegistry;
use Cel\Span\Span;
use Cel\Syntax\Unary\UnaryOperatorKind;
use Cel\Value\BooleanValue;
use Cel\Value\IntegerValue;
use Cel\Value\ListValue;
use Cel\Value\MapValue;
use Cel\Value\MessageValue;
use Cel\Value\NullValue;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Cel\Value\ValueKind;
use Psl\Iter;
use Psl\Str;
use Psl\Str\Byte;
use Psl\Vec;
use Throwable;

use function array_fill;
use function array_pop;
use function array_slice;
use function count;

/**
 * Register-based virtual machine that executes compiled bytecode programs.
 *
 * @mago-expect lint:halstead
 */
final class VirtualMachine
{
    /**
     * Binary operator symbols for error messages, indexed by operator index.
     */
    private const array BINARY_OP_SYMBOLS = [
        0 => '<',
        1 => '<=',
        2 => '>',
        3 => '>=',
        4 => '==',
        5 => '!=',
        6 => 'in',
        7 => '+',
        8 => '-',
        9 => '*',
        10 => '/',
        11 => '%',
        12 => '&&',
        13 => '||',
    ];

    private bool $idempotent = true;

    /**
     * Pre-built binary operator lookup table: [$opIndex][$lhsKindValue][$rhsKindValue] => handler
     *
     * @var null|array<int, array<string, array<string, BinaryOperatorOverloadHandlerInterface>>>
     */
    private ?array $binaryOpLookup = null;

    public function __construct(
        private readonly Configuration $configuration,
        private readonly OperationRegistry $registry,
    ) {}

    public function wasIdempotent(): bool
    {
        return $this->idempotent;
    }

    /**
     * @throws EvaluationException
     *
     * @mago-expect analysis:possibly-static-access-on-interface
     */
    public function execute(Program $program, EnvironmentInterface $environment): Value
    {
        $this->idempotent = true;

        $this->binaryOpLookup ??= $this->registry->buildBinaryOperatorLookup();
        $binaryOpLookup = $this->binaryOpLookup;

        $code = $program->instructions;
        $constants = $program->constants;
        $strings = $program->strings;
        $spans = $program->spans;
        $messageFields = $program->messageFields;
        $codeLen = count($code);

        /** @var array<int, null|Value> $regs */
        $regs = array_fill(0, $program->registerCount, null);

        $currentEnv = $environment;
        /** @var list<EnvironmentInterface> $envStack */
        $envStack = [$environment];

        /** @var array<int, list<Value>> $iterItems */
        $iterItems = [];
        /** @var array<int, int> $iterIdx */
        $iterIdx = [];
        /** @var array<int, int> $iterLen */
        $iterLen = [];

        /** @var array<int, list<Value>> $listAccumulators */
        $listAccumulators = [];

        $msgFieldCounter = 0;

        /**
         * @var Value $TRUE
         * @var Value $FALSE
         * @var Value $NULL
         */
        static $TRUE, $FALSE, $NULL;

        $TRUE ??= new BooleanValue(true);
        $FALSE ??= new BooleanValue(false);
        $NULL ??= new NullValue();

        /** @var int<0, max> */
        $offset = 0;

        while ($offset < $codeLen) {
            $opcode = $code[$offset];
            $dst = $code[$offset + 1];
            $op1 = $code[$offset + 2];
            $op2 = $code[$offset + 3];
            $op3 = $code[$offset + 4];

            switch ($opcode) {
                case Opcode::LOAD_CONST:
                    $regs[$dst] = $constants[$op1];
                    $offset += 6;
                    break;
                case Opcode::LOAD_VAR:
                    $value = $currentEnv->getVariable($strings[$op1]);
                    if (null === $value) {
                        throw new NoSuchVariableException(
                            Str\format('Variable `%s` is not defined in the environment', $strings[$op1]),
                            $spans[$code[$offset + 5]],
                        );
                    }
                    $regs[$dst] = $value;
                    $offset += 6;
                    break;
                case Opcode::LOAD_TRUE:
                    $regs[$dst] = $TRUE;
                    $offset += 6;
                    break;
                case Opcode::LOAD_FALSE:
                    $regs[$dst] = $FALSE;
                    $offset += 6;
                    break;
                case Opcode::LOAD_NULL:
                    $regs[$dst] = $NULL;
                    $offset += 6;
                    break;
                case Opcode::NEGATE:
                    /** @var Value */
                    $operand = $regs[$op1];
                    $handler = $this->registry->getUnaryOperator(UnaryOperatorKind::Negate, $operand->getKind());
                    if (null === $handler) {
                        throw new NoSuchOverloadException(
                            Str\format('No such overload for -`%s`', $operand->getType()),
                            $spans[$code[$offset + 5]],
                        );
                    }
                    $regs[$dst] = $handler($spans[$code[$offset + 5]], $operand);
                    $offset += 6;
                    break;
                case Opcode::NOT:
                    /** @var Value */
                    $operand = $regs[$op1];
                    if ($operand instanceof BooleanValue) {
                        $regs[$dst] = $operand->value ? $FALSE : $TRUE;
                        $offset += 6;
                        break;
                    }
                    $handler = $this->registry->getUnaryOperator(UnaryOperatorKind::Not, $operand->getKind());
                    if (null === $handler) {
                        throw new NoSuchOverloadException(
                            Str\format('No such overload for !`%s`', $operand->getType()),
                            $spans[$code[$offset + 5]],
                        );
                    }
                    $regs[$dst] = $handler($spans[$code[$offset + 5]], $operand);
                    $offset += 6;
                    break;
                case Opcode::BINARY_OP:
                    /** @var Value */
                    $left = $regs[$op1];
                    /** @var Value */
                    $right = $regs[$op3];

                    $handler = $binaryOpLookup[$op2][$left->getKind()->value][$right->getKind()->value] ?? null;
                    if (null === $handler) {
                        throw new NoSuchOverloadException(
                            Str\format(
                                'No such overload for `%s` %s `%s`',
                                $left->getType(),
                                self::BINARY_OP_SYMBOLS[$op2],
                                $right->getType(),
                            ),
                            $spans[$code[$offset + 5]],
                        );
                    }

                    $regs[$dst] = $handler($spans[$code[$offset + 5]], $left, $right);
                    $offset += 6;
                    break;
                case Opcode::JUMP_IF_FALSE:
                    /** @var Value */
                    $val = $regs[$dst];
                    if ($val instanceof BooleanValue && !$val->value) {
                        $offset = $op1;
                    } else {
                        $offset += 6;
                    }
                    break;
                case Opcode::JUMP_IF_TRUE:
                    /** @var Value */
                    $val = $regs[$dst];
                    if ($val instanceof BooleanValue && $val->value) {
                        $offset = $op1;
                    } else {
                        $offset += 6;
                    }
                    break;
                case Opcode::JUMP:
                    $offset = $op1;
                    break;
                case Opcode::COND_CHECK:
                    /** @var Value */
                    $val = $regs[$dst];
                    if (!$val instanceof BooleanValue) {
                        if ($op2 === 1) {
                            throw new InvalidMacroCallException(
                                Str\format(
                                    'The `%s` macro predicate must result in a boolean, got `%s`',
                                    $strings[$op1],
                                    $val->getType(),
                                ),
                                $spans[$code[$offset + 5]],
                            );
                        }
                        if ($op2 === 2) {
                            throw new InvalidMacroCallException(
                                Str\format(
                                    'The `%s` macro filter must result in a boolean, got `%s`',
                                    $strings[$op1],
                                    $val->getType(),
                                ),
                                $spans[$code[$offset + 5]],
                            );
                        }
                        throw new InvalidConditionTypeException(
                            Str\format('Condition must be boolean, got `%s`', $val->getType()),
                            $spans[$code[$offset + 5]],
                        );
                    }
                    $offset += 6;
                    break;
                case Opcode::MEMBER_ACCESS:
                    /** @var Value */
                    $operand = $regs[$op1];
                    $fieldName = $strings[$op2];

                    if ($operand instanceof MapValue) {
                        $field = $operand->get($fieldName);
                        if (null === $field) {
                            throw new NoSuchKeyException(
                                Str\format('Key `%s` does not exist in map', $fieldName),
                                $spans[$code[$offset + 5]],
                            );
                        }
                        $regs[$dst] = $field;
                    } elseif ($operand instanceof MessageValue) {
                        $field = $operand->getField($fieldName);
                        if (null === $field) {
                            throw new NoSuchKeyException(
                                Str\format(
                                    'Field `%s` does not exist on message of type `%s`',
                                    $fieldName,
                                    $operand->message::class,
                                ),
                                $spans[$code[$offset + 5]],
                            );
                        }
                        $regs[$dst] = $field;
                    } else {
                        throw new NoSuchOverloadException(
                            Str\format('Cannot access member `%s` on type `%s`', $fieldName, $operand->getType()),
                            $spans[$code[$offset + 5]],
                        );
                    }
                    $offset += 6;
                    break;
                case Opcode::INDEX:
                    /** @var Value */
                    $operand = $regs[$op1];
                    /** @var Value */
                    $index = $regs[$op2];

                    if ($operand instanceof ListValue) {
                        if (!$index instanceof IntegerValue) {
                            throw new NoSuchOverloadException(
                                Str\format('List indices must be integer, got `%s`', $index->getType()),
                                $spans[$code[$offset + 5]],
                            );
                        }
                        $listLen = count($operand->value);
                        if ($index->value < 0 || $index->value >= $listLen) {
                            throw new NoSuchKeyException(
                                Str\format(
                                    'Index `%d` is out of bounds for list of length `%d`',
                                    $index->value,
                                    $listLen,
                                ),
                                $spans[$code[$offset + 5]],
                            );
                        }
                        $regs[$dst] = $operand->value[$index->value];
                    } elseif ($operand instanceof MapValue) {
                        if (!$index instanceof StringValue && !$index instanceof IntegerValue) {
                            throw new NoSuchOverloadException(
                                Str\format('Map keys must be string or integer, got `%s`', $index->getType()),
                                $spans[$code[$offset + 5]],
                            );
                        }
                        $field = $operand->get($index->value);
                        if (null === $field) {
                            throw new NoSuchKeyException(
                                Str\format('Key `%s` does not exist in map', $index->value),
                                $spans[$code[$offset + 5]],
                            );
                        }
                        $regs[$dst] = $field;
                    } elseif ($operand instanceof MessageValue) {
                        if (!$index instanceof StringValue) {
                            throw new NoSuchOverloadException(
                                Str\format('Message fields must be accessed by string, got `%s`', $index->getType()),
                                $spans[$code[$offset + 5]],
                            );
                        }
                        $field = $operand->getField($index->value);
                        if (null === $field) {
                            throw new NoSuchKeyException(
                                Str\format(
                                    'Field `%s` does not exist on message of type `%s`',
                                    $index->value,
                                    $operand->message::class,
                                ),
                                $spans[$code[$offset + 5]],
                            );
                        }
                        $regs[$dst] = $field;
                    } else {
                        throw new NoSuchOverloadException(
                            Str\format(
                                'Indexing is only supported on lists, maps, and messages, got `%s`',
                                $operand->getType(),
                            ),
                            $spans[$code[$offset + 5]],
                        );
                    }
                    $offset += 6;
                    break;
                case Opcode::CALL:
                    $funcName = $strings[$op1];
                    $argCount = $op3;

                    /** @var list<Value> $arguments */
                    $arguments = $argCount > 0 ? array_slice($regs, $op2, $argCount) : [];
                    $function = $this->registry->getFunction($funcName, $arguments);
                    if (null === $function) {
                        $span = $spans[$code[$offset + 5]];
                        $availableSignatures = $this->registry->getFunctionSignatures($funcName);
                        if (null === $availableSignatures) {
                            throw new NoSuchFunctionException(
                                Str\format('Function `%s` is not defined', $funcName),
                                $span,
                            );
                        }

                        $argumentKinds = Vec\map($arguments, static fn(Value $arg): ValueKind => $arg->getKind());

                        throw NoSuchOverloadException::forCall($funcName, $span, $availableSignatures, $argumentKinds);
                    }

                    [$isIdempotent, $callable] = $function;
                    if (!$isIdempotent) {
                        $this->idempotent = false;
                    }

                    $regs[$dst] = $callable($spans[$code[$offset + 5]], $arguments);
                    $offset += 6;
                    break;
                case Opcode::MAKE_LIST:
                    if ($op2 === 0) {
                        $regs[$dst] = new ListValue([]);
                        $listAccumulators[$dst] = [];
                    } else {
                        $regs[$dst] = new ListValue(array_slice($regs, $op1, $op2));
                    }
                    $offset += 6;
                    break;
                case Opcode::MAKE_MAP:
                    /** @var array<array-key, Value> $mapValues */
                    $mapValues = [];
                    for ($i = 0; $i < $op2; $i++) {
                        /** @var Value */
                        $key = $regs[$op1 + ($i * 2)];
                        /** @var Value */
                        $val = $regs[$op1 + ($i * 2) + 1];

                        if (!$key instanceof StringValue && !$key instanceof IntegerValue) {
                            throw new UnexpectedMapKeyTypeException(
                                Str\format('Map keys must be string, or integer, got `%s`', $key->getType()),
                                $spans[$code[$offset + 5]],
                            );
                        }

                        $mapValues[$key->value] = $val;
                    }
                    $regs[$dst] = new MapValue($mapValues);
                    $offset += 6;
                    break;
                case Opcode::MAKE_MSG:
                    $typename = $strings[$op1];
                    $baseReg = $op2;
                    $fieldCount = $op3;
                    $span = $spans[$code[$offset + 5]];

                    /** @var class-string<MessageInterface> */
                    $classname = Str\replace($typename, '.', '\\');

                    $foundClassname = $this->resolveMessageType($typename, $classname, $span);

                    $fieldNames = $messageFields[$msgFieldCounter++] ?? [];

                    /** @var array<string, Value> $fields */
                    $fields = [];
                    for ($i = 0; $i < $fieldCount; $i++) {
                        $fields[$fieldNames[$i]] = $regs[$baseReg + $i];
                    }

                    try {
                        $regs[$dst] = new MessageValue($foundClassname::fromCelFields($fields), $fields);
                    } catch (Throwable $e) {
                        throw new MessageConstructionException(
                            Str\format('Failed to create message of type `%s`: %s', $typename, $e->getMessage()),
                            $span,
                        );
                    }
                    $offset += 6;
                    break;
                case Opcode::HAS_FIELD:
                    /** @var Value */
                    $operand = $regs[$op1];
                    $fieldName = $strings[$op2];

                    if ($operand instanceof MessageValue) {
                        $regs[$dst] = $operand->hasField($fieldName) ? $TRUE : $FALSE;
                    } elseif ($operand instanceof MapValue) {
                        $regs[$dst] = $operand->has($fieldName) ? $TRUE : $FALSE;
                    } else {
                        throw new InvalidMacroCallException(
                            Str\format(
                                'The `has` macro requires a message or map operand, got `%s`',
                                $operand->getType(),
                            ),
                            $spans[$code[$offset + 5]],
                        );
                    }
                    $offset += 6;
                    break;
                case Opcode::ITER_INIT:
                    /** @var Value */
                    $target = $regs[$op1];
                    if ($target instanceof ListValue) {
                        $iterItems[$dst] = $target->value;
                        $iterIdx[$dst] = 0;
                        $iterLen[$dst] = count($target->value);
                    } elseif ($target instanceof MapValue) {
                        $items = Vec\map(Vec\keys($target->value), Value::from(...));
                        $iterItems[$dst] = $items;
                        $iterIdx[$dst] = 0;
                        $iterLen[$dst] = count($items);
                    } else {
                        throw new InvalidMacroCallException(
                            Str\format(
                                'The `%s` macro requires a list or map target, got `%s`',
                                $strings[$op2] ?? '',
                                $target->getType(),
                            ),
                            $spans[$code[$offset + 5]],
                        );
                    }
                    $offset += 6;
                    break;
                case Opcode::ITER_NEXT:
                    $idx = $iterIdx[$op1];

                    if ($idx >= $iterLen[$op1]) {
                        $offset = $op2;
                    } else {
                        $regs[$dst] = $iterItems[$op1][$idx];
                        $iterIdx[$op1] = $idx + 1;
                        $offset += 6;
                    }
                    break;
                case Opcode::SCOPE_PUSH:
                    $currentEnv = $currentEnv->fork();
                    $envStack[] = $currentEnv;
                    $offset += 6;
                    break;
                case Opcode::SCOPE_POP:
                    array_pop($envStack);
                    $currentEnv = $envStack[count($envStack) - 1];
                    $offset += 6;
                    break;
                case Opcode::BIND_VAR:
                    /** @var Value */
                    $value = $regs[$op2];

                    $currentEnv->addVariable($strings[$op1], $value);
                    $offset += 6;
                    break;
                case Opcode::LIST_APPEND:
                    $listAccumulators[$dst][] = $regs[$op1];
                    $offset += 6;
                    break;
                case Opcode::INT_INC:
                    $val = $regs[$dst];
                    if ($val instanceof IntegerValue) {
                        $regs[$dst] = new IntegerValue($val->value + 1);
                    }
                    $offset += 6;
                    break;
                case Opcode::MOVE:
                    if (isset($listAccumulators[$op1])) {
                        $regs[$dst] = new ListValue($listAccumulators[$op1]);
                        unset($listAccumulators[$op1]);
                    } else {
                        $regs[$dst] = $regs[$op1];
                    }
                    $offset += 6;
                    break;
                case Opcode::RETURN:
                    if (isset($listAccumulators[$op1])) {
                        return new ListValue($listAccumulators[$op1]);
                    }

                    /** @var Value */
                    return $regs[$op1];
                default:
                    throw new UnsupportedOperationException(
                        Str\format('Unknown opcode: %d', $opcode),
                        $spans[$code[$offset + 5]],
                    );
            }
        }

        throw new EvaluationException('Program ended without RETURN instruction', Span::zero());
    }

    /**
     * Resolves message type from typename/classname, following the same logic as the interpreter.
     *
     * @param class-string<\Cel\Message\MessageInterface> $classname
     *
     * @return class-string<\Cel\Message\MessageInterface>
     *
     * @throws EvaluationException
     */
    private function resolveMessageType(string $typename, string $classname, Span $span): string
    {
        if ([] === $this->configuration->allowedMessageClasses) {
            throw new NoSuchTypeException(
                Str\format('Message type `%s` does not exist or is not allowed per configuration.', $typename),
                $span,
            );
        }

        $foundClassname = null;
        foreach ($this->configuration->messageClassAliases as $typeAlias => $targetClassname) {
            if (Byte\compare_ci($typename, $typeAlias) !== 0) {
                continue;
            }

            $foundClassname = $targetClassname;
            break;
        }

        if (null === $foundClassname) {
            foreach ($this->configuration->allowedMessageClasses as $allowedClassname) {
                if (Byte\compare_ci($classname, $allowedClassname) !== 0) {
                    continue;
                }

                $foundClassname = $allowedClassname;
                break;
            }

            if (
                null !== $foundClassname
                && $this->configuration->enforceMessageClassAliases
                && Iter\contains_key($this->configuration->messageClassesToAliases, $foundClassname)
            ) {
                throw new NoSuchTypeException(
                    Str\format('Message type `%s` does not exist or is not allowed per configuration.', $typename),
                    $span,
                );
            }
        }

        if (null === $foundClassname) {
            throw new NoSuchTypeException(
                Str\format('Message type `%s` does not exist or is not allowed per configuration.', $typename),
                $span,
            );
        }

        return $foundClassname;
    }
}
