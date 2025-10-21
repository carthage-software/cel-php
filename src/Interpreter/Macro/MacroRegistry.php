<?php

declare(strict_types=1);

namespace Cel\Interpreter\Macro;

use Cel\Exception\EvaluationException;
use Cel\Exception\InvalidMacroCallException;
use Cel\Syntax\Member\CallExpression;
use Cel\Value\Value;

/**
 * Registry for CEL macros.
 *
 * Maintains a collection of macros and provides lookup functionality
 * for macro execution.
 */
final class MacroRegistry
{
    /**
     * @var array<string, MacroInterface>
     */
    private array $macros = [];

    /**
     * Registers a macro.
     *
     * @param MacroInterface $macro The macro to register
     */
    public function register(MacroInterface $macro): void
    {
        $this->macros[$macro->getName()] = $macro;
    }

    /**
     * Attempts to execute a macro for the given call expression.
     *
     * @param CallExpression $call The call expression
     * @param MacroContextInterface $context The execution context
     *
     * @return Value|null The result of the macro, or null if no matching macro was found
     *
     * @throws InvalidMacroCallException If the macro call is invalid
     * @throws EvaluationException If evaluation fails
     */
    public function tryExecute(CallExpression $call, MacroContextInterface $context): null|Value
    {
        $functionName = $call->function->name;

        // Check if we have a macro with this name
        $macro = $this->macros[$functionName] ?? null;
        if (null === $macro) {
            return null;
        }

        // Check if the macro can handle this specific call
        if (!$macro->canHandle($call)) {
            return null;
        }

        // Execute the macro
        return $macro->execute($call, $context);
    }

    /**
     * Checks if a macro with the given name is registered.
     *
     * @param string $name The macro name
     *
     * @return bool True if a macro with this name exists
     */
    public function has(string $name): bool
    {
        return isset($this->macros[$name]);
    }
}
