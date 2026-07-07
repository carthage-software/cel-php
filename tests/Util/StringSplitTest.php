<?php

declare(strict_types=1);

namespace Cel\Tests\Util;

use Cel\Util\StringSplit;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class StringSplitTest extends TestCase
{
    /**
     * @param list<string> $expected
     */
    #[DataProvider('provideCharacterCases')]
    public function testCharacters(string $string, null|int $limit, bool $bytes, array $expected): void
    {
        static::assertSame($expected, StringSplit::characters($string, $limit, $bytes));
    }

    /**
     * @return iterable<string, array{string, null|int, bool, list<string>}>
     */
    public static function provideCharacterCases(): iterable
    {
        yield 'ascii, no limit' => ['abc', null, false, ['a', 'b', 'c']];
        yield 'multibyte code points, no limit' => ['é', null, false, ['é']];
        yield 'multibyte bytes, no limit' => ['é', null, true, ["\xc3", "\xa9"]];
        yield 'limit past length returns full split' => ['ab', 5, false, ['a', 'b']];
        yield 'limit below length keeps remainder whole' => ['abcd', 2, false, ['a', 'bcd']];
        yield 'limit of one is the whole string' => ['abc', 1, false, ['abc']];
        yield 'empty string, no limit' => ['', null, false, []];
        yield 'empty string, zero limit' => ['', 0, false, []];
        yield 'multibyte length drives the branch' => ['éé', 3, false, ['é', 'é']];
        yield 'bytes head and remainder' => ['abcd', 2, true, ['a', 'bcd']];
        yield 'multibyte inside the head' => ['éabc', 3, false, ['é', 'a', 'bc']];
        yield 'multibyte inside the remainder' => ['aébc', 2, false, ['a', 'ébc']];
        yield 'multibyte head in bytes mode' => ['éabc', 3, true, ["\xc3", "\xa9", 'abc']];
    }
}
