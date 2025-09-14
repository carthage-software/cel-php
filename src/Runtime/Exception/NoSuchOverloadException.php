<?php

declare(strict_types=1);

namespace Cel\Runtime\Exception;

use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Member\CallExpression;
use Psl\Iter;
use Psl\Str;
use Psl\Vec;

use function array_pop;

/**
 * Exception thrown when a function is called with an invalid set of argument types.
 */
final class NoSuchOverloadException extends RuntimeException
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
        $message = Str\format(
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
        $formatted = Vec\map($signatures, self::formatKinds(...));
        if (Iter\count($formatted) === 1) {
            return '`' . Str\join($formatted, '') . '`';
        }

        /** @var string $last */
        $last = array_pop($formatted);

        return Str\format('`%s`, or `%s`', Str\join($formatted, '`, `'), $last);
    }

    /**
     * Formats a single list of ValueKinds into a signature string.
     * e.g., `(string, int)`
     *
     * @param list<ValueKind> $kinds
     */
    private static function formatKinds(array $kinds): string
    {
        return Str\format('(%s)', Str\join(Vec\map($kinds, static fn(ValueKind $kind): string => $kind->value), ', '));
    }
}
