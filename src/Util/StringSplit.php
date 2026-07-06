<?php

declare(strict_types=1);

namespace Cel\Util;

use function mb_str_split;
use function mb_strlen;
use function mb_substr;
use function str_split;
use function strlen;
use function substr;

/**
 * Splits a string on an empty delimiter into single characters (or bytes),
 * honouring an optional limit, matching how the string functions behave when
 * asked to split with no delimiter.
 */
final readonly class StringSplit
{
    private function __construct() {}

    /**
     * @return list<string>
     */
    public static function characters(string $string, null|int $limit, bool $bytes): array
    {
        $length = $bytes ? strlen($string) : mb_strlen($string);
        if (null === $limit || $limit >= $length) {
            return self::chunk($string, $bytes);
        }

        // Keep the first ($limit - 1) characters, then the unsplit remainder as
        // a single element (which covers the $limit === 1 case naturally).
        $head = $bytes ? substr($string, 0, $limit - 1) : mb_substr($string, 0, $limit - 1);
        $result = self::chunk($head, $bytes);
        $result[] = $bytes ? substr($string, $limit - 1) : mb_substr($string, $limit - 1);

        return $result;
    }

    /**
     * @return list<string>
     */
    private static function chunk(string $string, bool $bytes): array
    {
        if ('' === $string) {
            return [];
        }

        return $bytes ? str_split($string) : mb_str_split($string);
    }
}
