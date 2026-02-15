<?php

declare(strict_types=1);

namespace Cel\Runtime;

use Cel\Exception\ConflictingFunctionSignatureException;
use Cel\Extension\ExtensionInterface;
use Cel\Function\FunctionInterface;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Operator\BinaryOperatorOverloadHandlerInterface;
use Cel\Operator\BinaryOperatorOverloadInterface;
use Cel\Operator\UnaryOperatorOverloadHandlerInterface;
use Cel\Operator\UnaryOperatorOverloadInterface;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Cel\Syntax\Unary\UnaryOperatorKind;
use Cel\Value\Value;
use Cel\Value\ValueKind;
use Override;
use Psl\Default\DefaultInterface;
use Psl\Str;
use Psl\Vec;

use function count;

final class OperationRegistry implements DefaultInterface
{
    /**
     * A map for optimized function lookups:
     *
     * `[function_name][signature_hash] => ['callable' => handler, 'signature' => list<ValueKind>, 'is_idempotent' => bool]`
     *
     * @var array<string, array<string, array{
     *     callable: FunctionOverloadHandlerInterface,
     *     signature: list<ValueKind>,
     *     is_idempotent: bool,
     * }>>
     */
    private array $functionOverloads = [];

    /**
     * A map for binary operator overloads:
     *
     * `[operator_kind_name][lhs_kind_value][rhs_kind_value] => handler`
     *
     * @var array<string, array<string, array<string, BinaryOperatorOverloadHandlerInterface>>>
     */
    private array $binaryOperatorOverloads = [];

    /**
     * A map for unary operator overloads:
     *
     * `[operator_kind_name][operand_kind_value] => handler`
     *
     * @var array<string, array<string, UnaryOperatorOverloadHandlerInterface>>
     */
    private array $unaryOperatorOverloads = [];

    /**
     * Creates a default empty operation registry.
     *
     * @return static
     */
    #[Override]
    public static function default(): static
    {
        return new self();
    }

    /**
     * Registers all operations from the given extension.
     *
     * @throws ConflictingFunctionSignatureException if a function with the same name and signature already exists.
     */
    public function register(ExtensionInterface $extension): void
    {
        foreach ($extension->getFunctions() as $function) {
            $this->addFunction($function);
        }

        foreach ($extension->getBinaryOperatorOverloads() as $overload) {
            $this->addBinaryOperatorOverload($overload);
        }

        foreach ($extension->getUnaryOperatorOverloads() as $overload) {
            $this->addUnaryOperatorOverload($overload);
        }
    }

    /**
     * Adds a function to the registry.
     *
     * @throws ConflictingFunctionSignatureException if a function with the same name and signature already exists.
     */
    public function addFunction(FunctionInterface $function): void
    {
        $name = $function->getName();
        $isIdempotent = $function->isIdempotent();

        foreach ($function->getOverloads() as $signature => $callable) {
            $signatureHash = self::hashSignature($signature);

            if (isset($this->functionOverloads[$name][$signatureHash])) {
                throw new ConflictingFunctionSignatureException(Str\format(
                    'A function with the name "%s" and signature "(%s)" is already registered.',
                    $name,
                    $signatureHash,
                ));
            }

            $this->functionOverloads[$name][$signatureHash] = [
                'callable' => $callable,
                'signature' => $signature,
                'is_idempotent' => $isIdempotent,
            ];
        }
    }

    /**
     * Adds a binary operator overload to the registry.
     */
    public function addBinaryOperatorOverload(BinaryOperatorOverloadInterface $overload): void
    {
        $operator = $overload->getOperator();

        foreach ($overload->getOverloads() as $operand_kinds => $callable) {
            [$left_operand_kind, $right_operand_kind] = $operand_kinds;

            $this->binaryOperatorOverloads[$operator->name][$left_operand_kind->value][$right_operand_kind->value] =
                $callable;
        }
    }

    /**
     * Adds a unary operator overload to the registry.
     */
    public function addUnaryOperatorOverload(UnaryOperatorOverloadInterface $overload): void
    {
        $operator = $overload->getOperator();

        foreach ($overload->getOverloads() as $operand_kind => $callable) {
            $this->unaryOperatorOverloads[$operator->name][$operand_kind->value] = $callable;
        }
    }

    /**
     * Retrieves a function implementation based on the function name and provided arguments.
     *
     * If no matching function is found, returns null.
     * If a matching function is found, returns a tuple containing:
     *  - A boolean indicating if the function is idempotent.
     *  - A handler that implements the function logic.
     *
     * @param list<Value> $arguments
     *
     * @return null|list{bool, FunctionOverloadHandlerInterface}
     */
    public function getFunction(string $name, array $arguments): ?array
    {
        $candidates = $this->functionOverloads[$name] ?? [];

        if ([] === $candidates) {
            return null;
        }

        // Inline signature hash computation (avoid Vec\map + Str\join overhead)
        $argCount = count($arguments);
        if ($argCount === 0) {
            $signatureHash = '<no-args>';
        } else {
            $signatureHash = $arguments[0]->getKind()->value;
            for ($i = 1; $i < $argCount; $i++) {
                $signatureHash .= ',' . $arguments[$i]->getKind()->value;
            }
        }

        $function = $candidates[$signatureHash] ?? null;
        if (null === $function) {
            return null;
        }

        return [$function['is_idempotent'], $function['callable']];
    }

    /**
     * Retrieves a binary operator handler based on the operator kind and operand types.
     *
     * @return null|BinaryOperatorOverloadHandlerInterface
     */
    public function getBinaryOperator(
        BinaryOperatorKind $operator,
        ValueKind $lhsKind,
        ValueKind $rhsKind,
    ): ?BinaryOperatorOverloadHandlerInterface {
        return $this->binaryOperatorOverloads[$operator->name][$lhsKind->value][$rhsKind->value] ?? null;
    }

    /**
     * Retrieves a unary operator handler based on the operator kind and operand type.
     *
     * @return null|UnaryOperatorOverloadHandlerInterface
     */
    public function getUnaryOperator(
        UnaryOperatorKind $operator,
        ValueKind $operandKind,
    ): ?UnaryOperatorOverloadHandlerInterface {
        return $this->unaryOperatorOverloads[$operator->name][$operandKind->value] ?? null;
    }

    /**
     * @return null|non-empty-list<list<ValueKind>>
     */
    public function getFunctionSignatures(string $name): ?array
    {
        $candidates = $this->functionOverloads[$name] ?? null;

        if (null === $candidates) {
            return null;
        }

        $signatures = Vec\map(
            $candidates,
            /**
             * @param array{signature: list<ValueKind>, ...} $overload
             *
             * @return list<ValueKind>
             */
            static fn(array $overload): array => $overload['signature'],
        );

        if ([] === $signatures) {
            return null;
        }

        return $signatures;
    }

    /**
     * Builds a pre-computed lookup table for binary operators indexed by operator index.
     *
     * The returned table maps: [$operatorIndex][$lhsKindValue][$rhsKindValue] => handler
     *
     * This allows the VM to bypass BinaryOperatorKind enum reconstruction and
     * getBinaryOperator() method call overhead in the hot dispatch loop.
     *
     * @return array<int, array<string, array<string, BinaryOperatorOverloadHandlerInterface>>>
     */
    public function buildBinaryOperatorLookup(): array
    {
        // Maps Compiler::binaryOperatorIndex values to BinaryOperatorKind enum names
        static $indexToName = [
            0 => 'LessThan',
            1 => 'LessThanOrEqual',
            2 => 'GreaterThan',
            3 => 'GreaterThanOrEqual',
            4 => 'Equal',
            5 => 'NotEqual',
            6 => 'In',
            7 => 'Plus',
            8 => 'Minus',
            9 => 'Multiply',
            10 => 'Divide',
            11 => 'Modulo',
            12 => 'And',
            13 => 'Or',
        ];

        $table = [];
        foreach ($indexToName as $idx => $name) {
            $table[$idx] = $this->binaryOperatorOverloads[$name] ?? [];
        }

        return $table;
    }

    /**
     * @param list<ValueKind> $signature
     */
    private static function hashSignature(array $signature): string
    {
        if ([] === $signature) {
            return '<no-args>';
        }

        return Str\join(Vec\map($signature, static fn(ValueKind $kind): string => $kind->value), ',');
    }
}
