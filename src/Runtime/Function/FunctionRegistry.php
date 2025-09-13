<?php

declare(strict_types=1);

namespace Cel\Runtime\Function;

use Cel\Runtime\Exception\ConflictingFunctionSignatureException;
use Cel\Runtime\Extension\ExtensionInterface;
use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Member\CallExpression;
use Psl\Str;
use Psl\Vec;

use function array_map;

final class FunctionRegistry
{
    /**
     * A map for optimized lookups:
     *
     * `[function_name][signature_hash] => ['callable' => callable, 'signature' => list<ValueKind>, 'is_idempotent' => bool]`
     *
     * @var array<string, array<string, array{
     *     callable: (callable(CallExpression, list<Value>): Value),
     *     signature: list<ValueKind>,
     *     is_idempotent: bool,
     * }>>
     */
    private array $overloads = [];

    /**
     * Registers all functions from the given extension.
     *
     * @throws ConflictingFunctionSignatureException if a function with the same name and signature already exists.
     */
    public function register(ExtensionInterface $extension): void
    {
        foreach ($extension->getFunctions() as $function) {
            $this->add($function);
        }
    }

    /**
     * Adds a function to the registry.
     *
     * @throws ConflictingFunctionSignatureException if a function with the same name and signature already exists.
     */
    public function add(FunctionInterface $function): void
    {
        $name = $function->getName();
        $isIdempotent = $function->isIdempotent();

        foreach ($function->getOverloads() as $signature => $callable) {
            $signatureHash = self::hashSignature($signature);

            if (isset($this->overloads[$name][$signatureHash])) {
                throw new ConflictingFunctionSignatureException(Str\format(
                    'A function with the name "%s" and signature "(%s)" is already registered.',
                    $name,
                    $signatureHash,
                ));
            }

            $this->overloads[$name][$signatureHash] = [
                'callable' => $callable,
                'signature' => $signature,
                'is_idempotent' => $isIdempotent,
            ];
        }
    }

    /**
     * Retrieves a function implementation based on the call expression and provided arguments.
     *
     * If no matching function is found, returns null.
     * If a matching function is found, returns a tuple containing:
     *  - A boolean indicating if the function is idempotent.
     *  - A callable that takes the CallExpression and list of Values as arguments and returns a Value.
     *
     * @param list<Value> $arguments
     *
     * @return null|list{bool, (callable(CallExpression, list<Value>): Value)}
     */
    public function get(CallExpression $expression, array $arguments): null|array
    {
        $name = $expression->function->name;
        $candidates = $this->overloads[$name] ?? [];

        if ($candidates === []) {
            return null;
        }

        $providedArgumentKinds = Vec\map($arguments, static fn(Value $v): ValueKind => $v->getKind());
        $signatureHash = self::hashSignature($providedArgumentKinds);
        $function = $candidates[$signatureHash] ?? null;
        if ($function === null) {
            return null;
        }

        return [$function['is_idempotent'], $function['callable']];
    }

    /**
     * @return null|non-empty-list<list<ValueKind>>
     */
    public function getSignatures(CallExpression $expression): null|array
    {
        $name = $expression->function->name;
        $candidates = $this->overloads[$name] ?? null;

        if ($candidates === null) {
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

        if ($signatures === []) {
            return null;
        }

        return $signatures;
    }

    /**
     * @param list<ValueKind> $signature
     */
    private static function hashSignature(array $signature): string
    {
        if ($signature === []) {
            return '<no-args>';
        }

        return Str\join(Vec\map($signature, static fn(ValueKind $kind): string => $kind->value), ',');
    }
}
