<?php

declare(strict_types=1);

namespace Cel\VirtualMachine;

use Cel\Exception\InvalidMacroCallException;
use Cel\Exception\UnsupportedOperationException;
use Cel\Runtime\Configuration;
use Cel\Span\Span;
use Cel\Syntax\Aggregate\ListExpression;
use Cel\Syntax\Aggregate\MapExpression;
use Cel\Syntax\Aggregate\MessageExpression;
use Cel\Syntax\Binary\BinaryExpression;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Cel\Syntax\ConditionalExpression;
use Cel\Syntax\Expression;
use Cel\Syntax\Literal\BoolLiteralExpression;
use Cel\Syntax\Literal\BytesLiteralExpression;
use Cel\Syntax\Literal\FloatLiteralExpression;
use Cel\Syntax\Literal\IntegerLiteralExpression;
use Cel\Syntax\Literal\NullLiteralExpression;
use Cel\Syntax\Literal\StringLiteralExpression;
use Cel\Syntax\Literal\UnsignedIntegerLiteralExpression;
use Cel\Syntax\Member\CallExpression;
use Cel\Syntax\Member\IdentifierExpression;
use Cel\Syntax\Member\IndexExpression;
use Cel\Syntax\Member\MemberAccessExpression;
use Cel\Syntax\ParenthesizedExpression;
use Cel\Syntax\Unary\UnaryExpression;
use Cel\Syntax\Unary\UnaryOperatorKind;
use Cel\Value\BytesValue;
use Cel\Value\FloatValue;
use Cel\Value\IntegerValue;
use Cel\Value\StringValue;
use Cel\Value\UnsignedIntegerValue;
use Cel\Value\Value;
use Psl\Str;

use function array_push;
use function count;

/**
 * Compiles AST expressions into flat bytecode for the register VM.
 *
 * @mago-expect lint:kan-defect
 * @mago-expect lint:too-many-methods
 * @mago-expect lint:too-many-properties
 */
final class Compiler
{
    private int $nextRegister = 0;

    private int $instructionOffset = 0;

    /** @var list<int> */
    private array $instructions = [];

    /** @var list<Value> */
    private array $constants = [];

    /** @var list<string> */
    private array $strings = [];

    /** @var array<string, int> */
    private array $stringMap = [];

    /** @var list<Span> */
    private array $spans = [];

    /** @var list<list<string>> */
    private array $messageFields = [];

    private readonly bool $macrosEnabled;

    /**
     * 1-entry identity cache: skip recompilation when the same Expression object is passed.
     */
    private ?Expression $lastExpression = null;
    private ?Program $lastProgram = null;

    public function __construct(
        private readonly Configuration $configuration,
    ) {
        $this->macrosEnabled = $configuration->enableMacros;
    }

    public function compile(Expression $expression): Program
    {
        if ($this->lastExpression === $expression && $this->lastProgram !== null) {
            return $this->lastProgram;
        }

        $this->nextRegister = 0;
        $this->instructionOffset = 0;
        $this->instructions = [];
        $this->constants = [];
        $this->strings = [];
        $this->stringMap = [];
        $this->spans = [];
        $this->messageFields = [];

        $resultReg = $this->compileExpression($expression);
        $this->emit(Opcode::RETURN, 0, $resultReg, 0, 0, $this->addSpan($expression->getSpan()));

        $this->lastExpression = $expression;

        return $this->lastProgram = new Program(
            $this->instructions,
            $this->constants,
            $this->strings,
            $this->spans,
            $this->nextRegister,
            $this->messageFields,
        );
    }

    /**
     * @mago-expect lint:halstead
     */
    private function compileExpression(Expression $expression): int
    {
        if ($expression instanceof BinaryExpression) {
            return $this->compileBinary($expression);
        }

        if ($expression instanceof IdentifierExpression) {
            $dst = $this->allocReg();
            $nameIdx = $this->addString($expression->identifier->name);
            $this->emit(Opcode::LOAD_VAR, $dst, $nameIdx, 0, 0, $this->addSpan($expression->getSpan()));
            return $dst;
        }

        if ($expression instanceof MemberAccessExpression) {
            return $this->compileMemberAccess($expression);
        }

        if ($expression instanceof IntegerLiteralExpression) {
            return $this->emitLoadConst(new IntegerValue($expression->value), $expression->getSpan());
        }

        if ($expression instanceof CallExpression) {
            return $this->compileCall($expression);
        }

        if ($expression instanceof BoolLiteralExpression) {
            $dst = $this->allocReg();
            $opcode = $expression->value ? Opcode::LOAD_TRUE : Opcode::LOAD_FALSE;
            $this->emit($opcode, $dst, 0, 0, 0, $this->addSpan($expression->getSpan()));
            return $dst;
        }

        if ($expression instanceof StringLiteralExpression) {
            return $this->emitLoadConst(new StringValue($expression->value), $expression->getSpan());
        }

        if ($expression instanceof ParenthesizedExpression) {
            return $this->compileExpression($expression->expression);
        }

        if ($expression instanceof ConditionalExpression) {
            return $this->compileConditional($expression);
        }

        if ($expression instanceof UnaryExpression) {
            return $this->compileUnary($expression);
        }

        if ($expression instanceof FloatLiteralExpression) {
            return $this->emitLoadConst(new FloatValue($expression->value), $expression->getSpan());
        }

        if ($expression instanceof NullLiteralExpression) {
            $dst = $this->allocReg();
            $this->emit(Opcode::LOAD_NULL, $dst, 0, 0, 0, $this->addSpan($expression->getSpan()));
            return $dst;
        }

        if ($expression instanceof IndexExpression) {
            return $this->compileIndex($expression);
        }

        if ($expression instanceof BytesLiteralExpression) {
            return $this->emitLoadConst(new BytesValue($expression->value), $expression->getSpan());
        }

        if ($expression instanceof UnsignedIntegerLiteralExpression) {
            return $this->emitLoadConst(new UnsignedIntegerValue($expression->value), $expression->getSpan());
        }

        if ($expression instanceof ListExpression) {
            return $this->compileList($expression);
        }

        if ($expression instanceof MapExpression) {
            return $this->compileMap($expression);
        }

        if ($expression instanceof MessageExpression) {
            return $this->compileMessage($expression);
        }

        throw new UnsupportedOperationException(
            Str\format('Cannot compile expression of type `%s`', $expression::class),
            $expression->getSpan(),
        );
    }

    private function compileUnary(UnaryExpression $expression): int
    {
        $operandReg = $this->compileExpression($expression->operand);
        $dst = $this->allocReg();
        $opcode = match ($expression->operator->kind) {
            UnaryOperatorKind::Negate => Opcode::NEGATE,
            UnaryOperatorKind::Not => Opcode::NOT,
        };

        $this->emit($opcode, $dst, $operandReg, 0, 0, $this->addSpan($expression->getSpan()));
        return $dst;
    }

    private function compileBinary(BinaryExpression $expression): int
    {
        $operator = $expression->operator->kind;

        if ($operator === BinaryOperatorKind::And) {
            return $this->compileShortCircuitAnd($expression);
        }

        if ($operator === BinaryOperatorKind::Or) {
            return $this->compileShortCircuitOr($expression);
        }

        $leftReg = $this->compileExpression($expression->left);
        $rightReg = $this->compileExpression($expression->right);
        $dst = $this->allocReg();
        $opIdx = self::binaryOperatorIndex($operator);
        $this->emit(Opcode::BINARY_OP, $dst, $leftReg, $opIdx, $rightReg, $this->addSpan($expression->getSpan()));
        return $dst;
    }

    private function compileShortCircuitAnd(BinaryExpression $expression): int
    {
        $dst = $this->allocReg();

        $leftReg = $this->compileExpression($expression->left);
        $this->emit(Opcode::MOVE, $dst, $leftReg, 0, 0, $this->addSpan($expression->left->getSpan()));

        $jumpPc = $this->currentPc();
        $this->emit(Opcode::JUMP_IF_FALSE, $dst, 0, 0, 0, $this->addSpan($expression->getSpan()));

        $rightReg = $this->compileExpression($expression->right);
        $andIdx = self::binaryOperatorIndex(BinaryOperatorKind::And);
        $this->emit(Opcode::BINARY_OP, $dst, $leftReg, $andIdx, $rightReg, $this->addSpan($expression->getSpan()));

        $this->patchJumpTarget($jumpPc, $this->currentPc());

        return $dst;
    }

    private function compileShortCircuitOr(BinaryExpression $expression): int
    {
        $dst = $this->allocReg();

        $leftReg = $this->compileExpression($expression->left);
        $this->emit(Opcode::MOVE, $dst, $leftReg, 0, 0, $this->addSpan($expression->left->getSpan()));

        $jumpPc = $this->currentPc();
        $this->emit(Opcode::JUMP_IF_TRUE, $dst, 0, 0, 0, $this->addSpan($expression->getSpan()));

        $rightReg = $this->compileExpression($expression->right);
        $orIdx = self::binaryOperatorIndex(BinaryOperatorKind::Or);
        $this->emit(Opcode::BINARY_OP, $dst, $leftReg, $orIdx, $rightReg, $this->addSpan($expression->getSpan()));

        $this->patchJumpTarget($jumpPc, $this->currentPc());

        return $dst;
    }

    private function compileConditional(ConditionalExpression $expression): int
    {
        $dst = $this->allocReg();

        $condReg = $this->compileExpression($expression->condition);
        $this->emit(Opcode::COND_CHECK, $condReg, 0, 0, 0, $this->addSpan($expression->condition->getSpan()));

        $jumpToElsePc = $this->currentPc();
        $this->emit(Opcode::JUMP_IF_FALSE, $condReg, 0, 0, 0, $this->addSpan($expression->getSpan()));

        $thenReg = $this->compileExpression($expression->then);
        $this->emit(Opcode::MOVE, $dst, $thenReg, 0, 0, $this->addSpan($expression->then->getSpan()));

        $jumpToEndPc = $this->currentPc();
        $this->emit(Opcode::JUMP, 0, 0, 0, 0, $this->addSpan($expression->getSpan()));

        $this->patchJumpTarget($jumpToElsePc, $this->currentPc());

        $elseReg = $this->compileExpression($expression->else);
        $this->emit(Opcode::MOVE, $dst, $elseReg, 0, 0, $this->addSpan($expression->else->getSpan()));

        $this->patchJumpOp1($jumpToEndPc, $this->currentPc());

        return $dst;
    }

    private function compileMemberAccess(MemberAccessExpression $expression): int
    {
        $operandReg = $this->compileExpression($expression->operand);
        $dst = $this->allocReg();
        $fieldIdx = $this->addString($expression->field->name);
        $this->emit(Opcode::MEMBER_ACCESS, $dst, $operandReg, $fieldIdx, 0, $this->addSpan($expression->getSpan()));
        return $dst;
    }

    private function compileIndex(IndexExpression $expression): int
    {
        $operandReg = $this->compileExpression($expression->operand);
        $indexReg = $this->compileExpression($expression->index);
        $dst = $this->allocReg();
        $this->emit(Opcode::INDEX, $dst, $operandReg, $indexReg, 0, $this->addSpan($expression->getSpan()));
        return $dst;
    }

    private function compileCall(CallExpression $expression): int
    {
        $functionName = $expression->function->name;

        if ($this->macrosEnabled) {
            $macroResult = $this->tryCompileMacro($expression);
            if (null !== $macroResult) {
                return $macroResult;
            }
        }

        $argRegs = [];
        if (null !== $expression->target) {
            $argRegs[] = $this->compileExpression($expression->target);
        }

        foreach ($expression->arguments->elements as $arg) {
            $argRegs[] = $this->compileExpression($arg);
        }

        $argCount = count($argRegs);
        $baseReg = $this->nextRegister;
        foreach ($argRegs as $argReg) {
            $consecutiveReg = $this->allocReg();
            $this->emit(Opcode::MOVE, $consecutiveReg, $argReg, 0, 0, $this->addSpan($expression->getSpan()));
        }

        $dst = $this->allocReg();
        $funcNameIdx = $this->addString($functionName);
        $this->emit(Opcode::CALL, $dst, $funcNameIdx, $baseReg, $argCount, $this->addSpan($expression->getSpan()));
        return $dst;
    }

    private function tryCompileMacro(CallExpression $expression): ?int
    {
        $macroRegistry = $this->configuration->getMacroRegistry();
        $functionName = $expression->function->name;

        if (!$macroRegistry->has($functionName)) {
            return null;
        }

        return match ($functionName) {
            'has' => $this->compileHasMacro($expression),
            'all' => $this->compileIteratorMacro($expression, 'all'),
            'exists' => $this->compileIteratorMacro($expression, 'exists'),
            'exists_one' => $this->compileIteratorMacro($expression, 'exists_one'),
            'filter' => $this->compileIteratorMacro($expression, 'filter'),
            'map' => $this->compileIteratorMacro($expression, 'map'),
            default => null,
        };
    }

    private function compileHasMacro(CallExpression $expression): ?int
    {
        if (null !== $expression->target) {
            return null;
        }

        $argument = $expression->arguments->elements[0] ?? null;
        if (null === $argument || $expression->arguments->count() > 1) {
            return null;
        }

        if (!$argument instanceof MemberAccessExpression) {
            throw new InvalidMacroCallException(
                'The `has` macro requires a single member access expression as an argument.',
                $argument->getSpan(),
            );
        }

        $operandReg = $this->compileExpression($argument->operand);
        $dst = $this->allocReg();
        $fieldIdx = $this->addString($argument->field->name);
        $this->emit(Opcode::HAS_FIELD, $dst, $operandReg, $fieldIdx, 0, $this->addSpan($expression->getSpan()));
        return $dst;
    }

    private function compileIteratorMacro(CallExpression $expression, string $macroName): ?int
    {
        if (null === $expression->target) {
            return null;
        }

        $argCount = $expression->arguments->count();

        if ($macroName === 'map') {
            if ($argCount < 2 || $argCount > 3) {
                return null;
            }
        } else {
            if ($argCount !== 2) {
                return null;
            }
        }

        $nameExpr = $expression->arguments->elements[0];
        if (!$nameExpr instanceof IdentifierExpression) {
            return null;
        }

        $varName = $nameExpr->identifier->name;
        $targetReg = $this->compileExpression($expression->target);
        $spanIdx = $this->addSpan($expression->getSpan());
        $targetSpanIdx = $this->addSpan($expression->target->getSpan());
        $predSpanIdx = $this->addSpan($expression->arguments->elements[1]->getSpan());
        $macroNameIdx = $this->addString($macroName);

        return match ($macroName) {
            'all' => $this->compileAllMacro(
                $targetReg,
                $varName,
                $expression->arguments->elements[1],
                $spanIdx,
                $targetSpanIdx,
                $predSpanIdx,
                $macroNameIdx,
            ),
            'exists' => $this->compileExistsMacro(
                $targetReg,
                $varName,
                $expression->arguments->elements[1],
                $spanIdx,
                $targetSpanIdx,
                $predSpanIdx,
                $macroNameIdx,
            ),
            'exists_one' => $this->compileExistsOneMacro(
                $targetReg,
                $varName,
                $expression->arguments->elements[1],
                $spanIdx,
                $targetSpanIdx,
                $predSpanIdx,
                $macroNameIdx,
            ),
            'filter' => $this->compileFilterMacro(
                $targetReg,
                $varName,
                $expression->arguments->elements[1],
                $spanIdx,
                $targetSpanIdx,
                $predSpanIdx,
                $macroNameIdx,
            ),
            'map' => $this->compileMapMacro($expression, $targetReg, $varName, $spanIdx, $targetSpanIdx, $macroNameIdx),
            default => null,
        };
    }

    private function compileAllMacro(
        int $targetReg,
        string $varName,
        Expression $predicate,
        int $spanIdx,
        int $targetSpanIdx,
        int $predSpanIdx,
        int $macroNameIdx,
    ): int {
        $dst = $this->allocReg();
        $iterReg = $this->allocReg();
        $this->emit(Opcode::ITER_INIT, $iterReg, $targetReg, $macroNameIdx, 0, $targetSpanIdx);

        $accReg = $this->allocReg();
        $this->emit(Opcode::LOAD_TRUE, $accReg, 0, 0, 0, $spanIdx);

        $this->emit(Opcode::SCOPE_PUSH, 0, 0, 0, 0, $spanIdx);

        $loopPc = $this->currentPc();
        $itemReg = $this->allocReg();
        $this->emit(Opcode::ITER_NEXT, $itemReg, $iterReg, 0, 0, $spanIdx);

        $varNameIdx = $this->addString($varName);
        $this->emit(Opcode::BIND_VAR, 0, $varNameIdx, $itemReg, 0, $spanIdx);

        $predReg = $this->compileExpression($predicate);

        $this->emit(Opcode::COND_CHECK, $predReg, $macroNameIdx, 1, 0, $predSpanIdx);

        $this->emit(Opcode::JUMP_IF_TRUE, $predReg, $loopPc, 0, 0, $spanIdx);

        $this->emit(Opcode::LOAD_FALSE, $accReg, 0, 0, 0, $spanIdx);

        $jumpEndPc = $this->currentPc();
        $this->emit(Opcode::JUMP, 0, 0, 0, 0, $spanIdx);

        $endPc = $this->currentPc();

        $this->patchIterNextEnd($loopPc, $endPc);

        $this->patchJumpOp1($jumpEndPc, $endPc);

        $this->emit(Opcode::SCOPE_POP, 0, 0, 0, 0, $spanIdx);
        $this->emit(Opcode::MOVE, $dst, $accReg, 0, 0, $spanIdx);
        return $dst;
    }

    private function compileExistsMacro(
        int $targetReg,
        string $varName,
        Expression $predicate,
        int $spanIdx,
        int $targetSpanIdx,
        int $predSpanIdx,
        int $macroNameIdx,
    ): int {
        $dst = $this->allocReg();
        $iterReg = $this->allocReg();
        $this->emit(Opcode::ITER_INIT, $iterReg, $targetReg, $macroNameIdx, 0, $targetSpanIdx);

        $accReg = $this->allocReg();
        $this->emit(Opcode::LOAD_FALSE, $accReg, 0, 0, 0, $spanIdx);

        $this->emit(Opcode::SCOPE_PUSH, 0, 0, 0, 0, $spanIdx);

        $loopPc = $this->currentPc();
        $itemReg = $this->allocReg();
        $this->emit(Opcode::ITER_NEXT, $itemReg, $iterReg, 0, 0, $spanIdx);

        $varNameIdx = $this->addString($varName);
        $this->emit(Opcode::BIND_VAR, 0, $varNameIdx, $itemReg, 0, $spanIdx);

        $predReg = $this->compileExpression($predicate);

        $this->emit(Opcode::COND_CHECK, $predReg, $macroNameIdx, 1, 0, $predSpanIdx);

        $this->emit(Opcode::JUMP_IF_FALSE, $predReg, $loopPc, 0, 0, $spanIdx);

        $this->emit(Opcode::LOAD_TRUE, $accReg, 0, 0, 0, $spanIdx);

        $jumpEndPc = $this->currentPc();
        $this->emit(Opcode::JUMP, 0, 0, 0, 0, $spanIdx);

        $endPc = $this->currentPc();
        $this->patchIterNextEnd($loopPc, $endPc);
        $this->patchJumpOp1($jumpEndPc, $endPc);

        $this->emit(Opcode::SCOPE_POP, 0, 0, 0, 0, $spanIdx);
        $this->emit(Opcode::MOVE, $dst, $accReg, 0, 0, $spanIdx);
        return $dst;
    }

    private function compileExistsOneMacro(
        int $targetReg,
        string $varName,
        Expression $predicate,
        int $spanIdx,
        int $targetSpanIdx,
        int $predSpanIdx,
        int $macroNameIdx,
    ): int {
        $dst = $this->allocReg();
        $iterReg = $this->allocReg();
        $this->emit(Opcode::ITER_INIT, $iterReg, $targetReg, $macroNameIdx, 0, $targetSpanIdx);

        $countReg = $this->allocReg();
        $zeroIdx = $this->addConstant(new IntegerValue(0));
        $this->emit(Opcode::LOAD_CONST, $countReg, $zeroIdx, 0, 0, $spanIdx);

        $this->emit(Opcode::SCOPE_PUSH, 0, 0, 0, 0, $spanIdx);

        $loopPc = $this->currentPc();
        $itemReg = $this->allocReg();
        $this->emit(Opcode::ITER_NEXT, $itemReg, $iterReg, 0, 0, $spanIdx);

        $varNameIdx = $this->addString($varName);
        $this->emit(Opcode::BIND_VAR, 0, $varNameIdx, $itemReg, 0, $spanIdx);

        $predReg = $this->compileExpression($predicate);

        $this->emit(Opcode::COND_CHECK, $predReg, $macroNameIdx, 1, 0, $predSpanIdx);

        $this->emit(Opcode::JUMP_IF_FALSE, $predReg, $loopPc, 0, 0, $spanIdx);

        $this->emit(Opcode::INT_INC, $countReg, 0, 0, 0, $spanIdx);
        $this->emit(Opcode::JUMP, 0, $loopPc, 0, 0, $spanIdx);

        $endPc = $this->currentPc();
        $this->patchIterNextEnd($loopPc, $endPc);

        $this->emit(Opcode::SCOPE_POP, 0, 0, 0, 0, $spanIdx);

        $oneReg = $this->allocReg();
        $oneIdx = $this->addConstant(new IntegerValue(1));
        $this->emit(Opcode::LOAD_CONST, $oneReg, $oneIdx, 0, 0, $spanIdx);

        $equalIdx = self::binaryOperatorIndex(BinaryOperatorKind::Equal);
        $this->emit(Opcode::BINARY_OP, $dst, $countReg, $equalIdx, $oneReg, $spanIdx);
        return $dst;
    }

    private function compileFilterMacro(
        int $targetReg,
        string $varName,
        Expression $predicate,
        int $spanIdx,
        int $targetSpanIdx,
        int $predSpanIdx,
        int $macroNameIdx,
    ): int {
        $dst = $this->allocReg();
        $iterReg = $this->allocReg();
        $this->emit(Opcode::ITER_INIT, $iterReg, $targetReg, $macroNameIdx, 0, $targetSpanIdx);

        $accReg = $this->allocReg();
        $this->emit(Opcode::MAKE_LIST, $accReg, 0, 0, 0, $spanIdx);

        $this->emit(Opcode::SCOPE_PUSH, 0, 0, 0, 0, $spanIdx);

        $loopPc = $this->currentPc();
        $itemReg = $this->allocReg();
        $this->emit(Opcode::ITER_NEXT, $itemReg, $iterReg, 0, 0, $spanIdx);

        $varNameIdx = $this->addString($varName);
        $this->emit(Opcode::BIND_VAR, 0, $varNameIdx, $itemReg, 0, $spanIdx);

        $predReg = $this->compileExpression($predicate);

        $this->emit(Opcode::COND_CHECK, $predReg, $macroNameIdx, 1, 0, $predSpanIdx);

        $this->emit(Opcode::JUMP_IF_FALSE, $predReg, $loopPc, 0, 0, $spanIdx);

        $this->emit(Opcode::LIST_APPEND, $accReg, $itemReg, 0, 0, $spanIdx);
        $this->emit(Opcode::JUMP, 0, $loopPc, 0, 0, $spanIdx);

        $endPc = $this->currentPc();
        $this->patchIterNextEnd($loopPc, $endPc);

        $this->emit(Opcode::SCOPE_POP, 0, 0, 0, 0, $spanIdx);
        $this->emit(Opcode::MOVE, $dst, $accReg, 0, 0, $spanIdx);
        return $dst;
    }

    private function compileMapMacro(
        CallExpression $expression,
        int $targetReg,
        string $varName,
        int $spanIdx,
        int $targetSpanIdx,
        int $macroNameIdx,
    ): int {
        $dst = $this->allocReg();
        $iterReg = $this->allocReg();
        $this->emit(Opcode::ITER_INIT, $iterReg, $targetReg, $macroNameIdx, 0, $targetSpanIdx);

        $accReg = $this->allocReg();
        $this->emit(Opcode::MAKE_LIST, $accReg, 0, 0, 0, $spanIdx);

        $argCount = $expression->arguments->count();
        $filterCallback = 3 === $argCount ? $expression->arguments->elements[1] : null;
        $transformCallback = 3 === $argCount
            ? $expression->arguments->elements[2]
            : $expression->arguments->elements[1];

        $this->emit(Opcode::SCOPE_PUSH, 0, 0, 0, 0, $spanIdx);

        $loopPc = $this->currentPc();
        $itemReg = $this->allocReg();
        $this->emit(Opcode::ITER_NEXT, $itemReg, $iterReg, 0, 0, $spanIdx);

        $varNameIdx = $this->addString($varName);
        $this->emit(Opcode::BIND_VAR, 0, $varNameIdx, $itemReg, 0, $spanIdx);

        if (null !== $filterCallback) {
            $filterReg = $this->compileExpression($filterCallback);
            $filterSpanIdx = $this->addSpan($filterCallback->getSpan());
            $this->emit(Opcode::COND_CHECK, $filterReg, $macroNameIdx, 2, 0, $filterSpanIdx);
            $this->emit(Opcode::JUMP_IF_FALSE, $filterReg, $loopPc, 0, 0, $spanIdx);
        }

        $resultReg = $this->compileExpression($transformCallback);
        $this->emit(Opcode::LIST_APPEND, $accReg, $resultReg, 0, 0, $spanIdx);
        $this->emit(Opcode::JUMP, 0, $loopPc, 0, 0, $spanIdx);

        $endPc = $this->currentPc();
        $this->patchIterNextEnd($loopPc, $endPc);

        $this->emit(Opcode::SCOPE_POP, 0, 0, 0, 0, $spanIdx);
        $this->emit(Opcode::MOVE, $dst, $accReg, 0, 0, $spanIdx);
        return $dst;
    }

    private function compileList(ListExpression $expression): int
    {
        $elementRegs = [];
        foreach ($expression->elements->elements as $element) {
            $elementRegs[] = $this->compileExpression($element);
        }

        $elementCount = count($elementRegs);

        $baseReg = $this->nextRegister;
        foreach ($elementRegs as $elemReg) {
            $consecutiveReg = $this->allocReg();
            $this->emit(Opcode::MOVE, $consecutiveReg, $elemReg, 0, 0, $this->addSpan($expression->getSpan()));
        }

        $dst = $this->allocReg();
        $this->emit(Opcode::MAKE_LIST, $dst, $baseReg, $elementCount, 0, $this->addSpan($expression->getSpan()));
        return $dst;
    }

    private function compileMap(MapExpression $expression): int
    {
        $pairRegs = [];
        foreach ($expression->entries->elements as $entry) {
            $keyReg = $this->compileExpression($entry->key);
            $valReg = $this->compileExpression($entry->value);
            $pairRegs[] = $keyReg;
            $pairRegs[] = $valReg;
        }

        $pairCount = count($expression->entries->elements);

        $baseReg = $this->nextRegister;
        foreach ($pairRegs as $reg) {
            $consecutiveReg = $this->allocReg();
            $this->emit(Opcode::MOVE, $consecutiveReg, $reg, 0, 0, $this->addSpan($expression->getSpan()));
        }

        $dst = $this->allocReg();
        $this->emit(Opcode::MAKE_MAP, $dst, $baseReg, $pairCount, 0, $this->addSpan($expression->getSpan()));
        return $dst;
    }

    private function compileMessage(MessageExpression $expression): int
    {
        $typename = $expression->selector->name;
        foreach ($expression->followingSelectors->elements as $selector) {
            $typename .= '.' . $selector->name;
        }

        $typeNameIdx = $this->addString($typename);

        $classname = $expression->selector->name;
        foreach ($expression->followingSelectors->elements as $selector) {
            $classname .= '\\' . $selector->name;
        }

        $classnameIdx = $this->addString($classname);

        $fieldNames = [];
        $fieldRegs = [];
        foreach ($expression->initializers->elements as $initializer) {
            $fieldNames[] = $initializer->field->name;
            $fieldRegs[] = $this->compileExpression($initializer->value);
        }

        $fieldCount = count($fieldNames);

        $msgFieldsIdx = count($this->messageFields);
        $this->messageFields[] = $fieldNames;

        $baseReg = $this->nextRegister;
        foreach ($fieldRegs as $reg) {
            $consecutiveReg = $this->allocReg();
            $this->emit(Opcode::MOVE, $consecutiveReg, $reg, 0, 0, $this->addSpan($expression->getSpan()));
        }

        $dst = $this->allocReg();

        $this->emit(
            Opcode::MAKE_MSG,
            $dst,
            $typeNameIdx,
            $baseReg,
            $fieldCount,
            $this->addSpan($expression->getSpan()),
        );

        return $dst;
    }

    private function allocReg(): int
    {
        return $this->nextRegister++;
    }

    private function currentPc(): int
    {
        return $this->instructionOffset;
    }

    private function emit(int $opcode, int $dst, int $op1, int $op2, int $op3, int $spanIdx): void
    {
        array_push($this->instructions, $opcode, $dst, $op1, $op2, $op3, $spanIdx);
        $this->instructionOffset += 6;
    }

    private function emitLoadConst(Value $value, Span $span): int
    {
        $dst = $this->allocReg();
        $constIdx = $this->addConstant($value);
        $this->emit(Opcode::LOAD_CONST, $dst, $constIdx, 0, 0, $this->addSpan($span));
        return $dst;
    }

    private function addConstant(Value $value): int
    {
        $this->constants[] = $value;

        return count($this->constants) - 1;
    }

    private function addString(string $str): int
    {
        if (isset($this->stringMap[$str])) {
            return $this->stringMap[$str];
        }

        $idx = count($this->strings);
        $this->strings[] = $str;
        $this->stringMap[$str] = $idx;
        return $idx;
    }

    private function addSpan(Span $span): int
    {
        $this->spans[] = $span;

        return count($this->spans) - 1;
    }

    /**
     * Patches a JUMP_IF_FALSE or JUMP_IF_TRUE instruction's op1 field.
     *
     * @param int $offset The byte offset of the instruction to patch.
     * @param int $target The byte offset of the target instruction.
     */
    private function patchJumpTarget(int $offset, int $target): void
    {
        $this->instructions[$offset + 2] = $target;
    }

    /**
     * Patches a JUMP instruction's op1 field.
     *
     * @param int $offset The byte offset of the instruction to patch.
     * @param int $target The byte offset of the target instruction.
     */
    private function patchJumpOp1(int $offset, int $target): void
    {
        $this->instructions[$offset + 2] = $target;
    }

    /**
     * Patches an ITER_NEXT instruction's op2 (jump-when-done) field.
     * ITER_NEXT uses: dst=itemReg, op1=iterReg, op2=jumpTarget
     *
     * @param int $offset The byte offset of the instruction to patch.
     * @param int $target The byte offset of the target instruction.
     */
    private function patchIterNextEnd(int $offset, int $target): void
    {
        $this->instructions[$offset + 3] = $target;
    }

    private static function binaryOperatorIndex(BinaryOperatorKind $kind): int
    {
        return match ($kind) {
            BinaryOperatorKind::LessThan => 0,
            BinaryOperatorKind::LessThanOrEqual => 1,
            BinaryOperatorKind::GreaterThan => 2,
            BinaryOperatorKind::GreaterThanOrEqual => 3,
            BinaryOperatorKind::Equal => 4,
            BinaryOperatorKind::NotEqual => 5,
            BinaryOperatorKind::In => 6,
            BinaryOperatorKind::Plus => 7,
            BinaryOperatorKind::Minus => 8,
            BinaryOperatorKind::Multiply => 9,
            BinaryOperatorKind::Divide => 10,
            BinaryOperatorKind::Modulo => 11,
            BinaryOperatorKind::And => 12,
            BinaryOperatorKind::Or => 13,
        };
    }
}
