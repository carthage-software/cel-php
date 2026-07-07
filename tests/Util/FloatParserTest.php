<?php

declare(strict_types=1);

namespace Cel\Tests\Util;

use Cel\Util\FloatParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FloatParserTest extends TestCase
{
    #[DataProvider('provideParseCases')]
    public function testTryParse(string $input, null|float $expected): void
    {
        static::assertSame($expected, FloatParser::tryParse($input));
    }

    /**
     * @return iterable<string, array{string, null|float}>
     */
    public static function provideParseCases(): iterable
    {
        yield 'integer digits' => ['123', 123.0];
        yield 'zero' => ['0', 0.0];
        yield 'decimal' => ['1.23', 1.23];
        yield 'trailing dot' => ['1.', 1.0];
        yield 'leading dot' => ['.5', 0.5];
        yield 'explicit plus sign' => ['+42', 42.0];
        yield 'explicit minus sign' => ['-3.14', -3.14];
        yield 'scientific lowercase' => ['1e10', 1e10];
        yield 'scientific uppercase with sign' => ['1.5E-3', 0.0015];
        yield 'empty string' => ['', null];
        yield 'letters' => ['abc', null];
        yield 'two dots' => ['1.2.3', null];
        yield 'leading junk' => ['abc123', null];
        yield 'trailing junk' => ['1.5abc', null];
        yield 'leading space' => [' 1', null];
        yield 'trailing space' => ['1 ', null];
        yield 'hex is not a float' => ['0x1A', null];
    }
}
