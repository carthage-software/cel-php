<?php

declare(strict_types=1);

namespace Cel\Util;

use Cel\Exception\NumberFormatException;

use function bcadd;
use function bcdiv;
use function bcmod;
use function bcmul;
use function bcpow;
use function intdiv;
use function ord;
use function str_split;
use function stripos;
use function strlen;
use function substr;

use const PHP_INT_MAX;

/**
 * Radix conversion for the CEL math extension, using digits `0-9a-zA-Z` for
 * bases up to 62.
 *
 * @internal
 */
final readonly class NumberBase
{
    private const string ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    private function __construct() {}

    /**
     * Parses a string in the given base to a native integer.
     *
     * @throws NumberFormatException If the string contains a digit invalid for the base or the value
     *                              overflows the integer range.
     */
    public static function fromBase(string $number, int $fromBase): int
    {
        // @mago-expect analysis:unhandled-thrown-type(2) - the base is a positive radix, so division by zero cannot occur.
        $limit = intdiv(PHP_INT_MAX, $fromBase);
        $result = 0;
        foreach (str_split($number) as $digit) {
            $ordinal = ord($digit);
            if ($ordinal >= 48 && $ordinal <= 57) {
                $value = $ordinal - 48;
            } elseif ($ordinal >= 97 && $ordinal <= 122) {
                $value = $ordinal - 87;
            } elseif ($ordinal >= 65 && $ordinal <= 90) {
                $value = $ordinal - 55;
            } else {
                throw NumberFormatException::forInvalidDigit($digit, $fromBase);
            }

            if ($fromBase <= $value) {
                throw NumberFormatException::forInvalidDigit($digit, $fromBase);
            }

            $previous = $result;
            $result = ($fromBase * $result) + $value;
            if ($previous > $limit || $previous > $result) {
                throw NumberFormatException::forOverflow($number, $fromBase);
            }
        }

        return $result;
    }

    /**
     * Formats a native integer as a string in the given base.
     */
    public static function toBase(int $number, int $base): string
    {
        $result = '';
        do {
            // @mago-expect analysis:unhandled-thrown-type(2) - the base is a positive radix, so division by zero cannot occur.
            $quotient = intdiv($number, $base);
            /** @var int<0, 61> $index */
            $index = $number - ($quotient * $base);
            $result = self::ALPHABET[$index] . $result;
            $number = $quotient;
        } while (0 !== $number);

        return $result;
    }

    /**
     * Converts an arbitrary-precision string from one base to another.
     *
     * @throws NumberFormatException If the value contains a digit invalid for the source base.
     */
    public static function baseConvert(string $value, int $fromBase, int $toBase): string
    {
        $fromAlphabet = substr(self::ALPHABET, 0, $fromBase);
        $decimal = '0';
        $placeValue = bcpow((string) $fromBase, (string) (strlen($value) - 1));
        foreach (str_split($value) as $digit) {
            $digitValue = stripos($fromAlphabet, $digit);
            if (false === $digitValue) {
                throw NumberFormatException::forInvalidDigit($digit, $fromBase);
            }

            $decimal = bcadd($decimal, bcmul((string) $digitValue, $placeValue));
            // @mago-expect analysis:unhandled-thrown-type - the base is a positive radix, so division by zero cannot occur.
            $placeValue = bcdiv($placeValue, (string) $fromBase);
        }

        $toAlphabet = substr(self::ALPHABET, 0, $toBase);
        $result = '';
        do {
            $result = $toAlphabet[(int) bcmod($decimal, (string) $toBase)] . $result;
            $decimal = bcdiv($decimal, (string) $toBase);
        } while ('0' !== $decimal);

        return $result;
    }
}
