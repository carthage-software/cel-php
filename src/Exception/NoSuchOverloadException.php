<?php

declare(strict_types=1);

namespace Cel\Exception;

use Cel\Syntax\Member\CallExpression;
use Cel\Value\ValueKind;

use function array_map;
use function array_pop;
use function count;
use function implode;
use function sprintf;

/**
 * Exception thrown when a function is called with an invalid set of argument types.
 */
final class NoSuchOverloadException extends EvaluationException
{
    /**
     * @param CallExpression $expression The call expression that caused the error.
     * @param non-empty-list<list<ValueKind>> $availableSignatures A list of all valid signatures for the function.
     * @param list<ValueKind> $providedArgumentKinds The kinds of the arguments that were actually provided.
     */
    public static function forCall(
        CallExpression $expression,
        array $availableSignatures,
        array $providedArgumentKinds,
    ): static {
        $message = sprintf(
            'Invalid arguments for function "%s". Got `%s`, but expected one of: %s',
            $expression->function->name,
            self::formatKinds($providedArgumentKinds),
            self::formatSignatures($availableSignatures),
        );

        return new static($message, $expression->getSpan());
    }

    /**
     * Formats a list of signature arrays into a human-readable string.
     * e.g., `(string, bool), or (int, int)`
     *
     * @param non-empty-list<list<ValueKind>> $signatures
     */
    private static function formatSignatures(array $signatures): string
    {
        $formatted = array_map(self::formatKinds(...), $signatures);
        if (count($formatted) === 1) {
            return '`' . implode('', $formatted) . '`';
        }

        $last = array_pop($formatted);

        return sprintf('`%s`, or `%s`', implode('`, `', $formatted), $last);
    }

    /**
     * Formats a single list of ValueKinds into a signature string.
     * e.g., `(string, int)`
     *
     * @param list<ValueKind> $kinds
     */
    private static function formatKinds(array $kinds): string
    {
        return sprintf('(%s)', implode(', ', array_map(static fn(ValueKind $kind): string => $kind->value, $kinds)));
    }
}
