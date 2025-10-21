<?php

declare(strict_types=1);

namespace Cel\Tests\Extension;

use Cel\CommonExpressionLanguage;
use Cel\Extension\Decimal\DecimalExtension;
use Cel\Extension\Decimal\DecimalNumber;
use Cel\Value\MessageValue;
use Decimal\Decimal;
use Override;
use PHPUnit\Framework\TestCase;

use function extension_loaded;

/**
 * @mago-expect lint:too-many-methods
 */
final class DecimalExtensionTest extends TestCase
{
    #[Override]
    protected function setUp(): void
    {
        if (!extension_loaded('decimal')) {
            $this->markTestSkipped('ext-decimal is not installed.');
        }
    }

    private function createCel(): CommonExpressionLanguage
    {
        $cel = CommonExpressionLanguage::default();
        $cel->register(new DecimalExtension());
        return $cel;
    }

    public function testDecimalFunctionFromString(): void
    {
        $cel = $this->createCel();
        $expr = $cel->parseString('decimal("123.456")');
        $receipt = $cel->run($expr);

        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(DecimalNumber::class, $receipt->result->message);
        static::assertTrue($receipt->result->message->getInner()->equals(new Decimal('123.456')));
    }

    public function testDecimalFunctionFromInt(): void
    {
        $cel = $this->createCel();
        $expr = $cel->parseString('decimal(42)');
        $receipt = $cel->run($expr);

        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(DecimalNumber::class, $receipt->result->message);
        static::assertTrue($receipt->result->message->getInner()->equals(new Decimal('42')));
    }

    public function testDecimalFunctionFromUInt(): void
    {
        $cel = $this->createCel();
        $expr = $cel->parseString('decimal(42u)');
        $receipt = $cel->run($expr);

        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(DecimalNumber::class, $receipt->result->message);
        static::assertTrue($receipt->result->message->getInner()->equals(new Decimal('42')));
    }

    public function testDecimalFunctionFromFloat(): void
    {
        $cel = $this->createCel();
        $expr = $cel->parseString('decimal(3.14)');
        $receipt = $cel->run($expr);

        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(DecimalNumber::class, $receipt->result->message);
        static::assertTrue($receipt->result->message->getInner()->equals(new Decimal('3.14')));
    }

    public function testDecimalFunctionWithPrecision(): void
    {
        $cel = $this->createCel();
        $expr = $cel->parseString('decimal("1.23456789", 5)');
        $receipt = $cel->run($expr);

        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(DecimalNumber::class, $receipt->result->message);
        // Precision is set during creation
        // Note: ext-decimal may emit a warning about data loss when applying precision in PHP 8.4+
        // This is expected behavior from the extension when rounding occurs
        static::assertSame(5, $receipt->result->message->getInner()->precision());
    }

    public function testDecimalAddition(): void
    {
        $cel = $this->createCel();
        $expr = $cel->parseString('decimal("1.5") + decimal("2.5")');
        $receipt = $cel->run($expr);

        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(DecimalNumber::class, $receipt->result->message);
        static::assertTrue($receipt->result->message->getInner()->equals(new Decimal('4')));
    }

    public function testDecimalAdditionWithInt(): void
    {
        $cel = $this->createCel();
        $expr = $cel->parseString('decimal("10.5") + 5');
        $receipt = $cel->run($expr);

        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(DecimalNumber::class, $receipt->result->message);
        static::assertTrue($receipt->result->message->getInner()->equals(new Decimal('15.5')));
    }

    public function testIntAdditionWithDecimal(): void
    {
        $cel = $this->createCel();
        $expr = $cel->parseString('5 + decimal("10.5")');
        $receipt = $cel->run($expr);

        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(DecimalNumber::class, $receipt->result->message);
        static::assertTrue($receipt->result->message->getInner()->equals(new Decimal('15.5')));
    }

    public function testDecimalSubtraction(): void
    {
        $cel = $this->createCel();
        $expr = $cel->parseString('decimal("10.5") - decimal("3.2")');
        $receipt = $cel->run($expr);

        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(DecimalNumber::class, $receipt->result->message);
        static::assertTrue($receipt->result->message->getInner()->equals(new Decimal('7.3')));
    }

    public function testDecimalMultiplication(): void
    {
        $cel = $this->createCel();
        $expr = $cel->parseString('decimal("2.5") * decimal("4")');
        $receipt = $cel->run($expr);

        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(DecimalNumber::class, $receipt->result->message);
        static::assertTrue($receipt->result->message->getInner()->equals(new Decimal('10')));
    }

    public function testDecimalDivision(): void
    {
        $cel = $this->createCel();
        $expr = $cel->parseString('decimal("10") / decimal("4")');
        $receipt = $cel->run($expr);

        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(DecimalNumber::class, $receipt->result->message);
        static::assertTrue($receipt->result->message->getInner()->equals(new Decimal('2.5')));
    }

    public function testDecimalModulo(): void
    {
        $cel = $this->createCel();
        $expr = $cel->parseString('decimal("10") % decimal("3")');
        $receipt = $cel->run($expr);

        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(DecimalNumber::class, $receipt->result->message);
        static::assertTrue($receipt->result->message->getInner()->equals(new Decimal('1')));
    }

    public function testDecimalNegation(): void
    {
        $cel = $this->createCel();
        $expr = $cel->parseString('-decimal("5.5")');
        $receipt = $cel->run($expr);

        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(DecimalNumber::class, $receipt->result->message);
        static::assertTrue($receipt->result->message->getInner()->equals(new Decimal('-5.5')));
    }

    public function testDecimalLessThan(): void
    {
        $cel = $this->createCel();
        $expr = $cel->parseString('decimal("5.5") < decimal("10.5")');
        $receipt = $cel->run($expr);

        static::assertTrue($receipt->result->getRawValue());
    }

    public function testDecimalLessThanOrEqual(): void
    {
        $cel = $this->createCel();
        $expr = $cel->parseString('decimal("10.5") <= decimal("10.5")');
        $receipt = $cel->run($expr);

        static::assertTrue($receipt->result->getRawValue());
    }

    public function testDecimalGreaterThan(): void
    {
        $cel = $this->createCel();
        $expr = $cel->parseString('decimal("10.5") > decimal("5.5")');
        $receipt = $cel->run($expr);

        static::assertTrue($receipt->result->getRawValue());
    }

    public function testDecimalGreaterThanOrEqual(): void
    {
        $cel = $this->createCel();
        $expr = $cel->parseString('decimal("5.5") >= decimal("5.5")');
        $receipt = $cel->run($expr);

        static::assertTrue($receipt->result->getRawValue());
    }

    public function testDecimalEquality(): void
    {
        $cel = $this->createCel();
        $expr = $cel->parseString('decimal("5.5") == decimal("5.5")');
        $receipt = $cel->run($expr);

        static::assertTrue($receipt->result->getRawValue());
    }

    public function testDecimalInequality(): void
    {
        $cel = $this->createCel();
        $expr = $cel->parseString('decimal("5.5") != decimal("10.5")');
        $receipt = $cel->run($expr);

        static::assertTrue($receipt->result->getRawValue());
    }

    public function testDecimalComparisonWithInt(): void
    {
        $cel = $this->createCel();
        $expr = $cel->parseString('decimal("10.5") > 5');
        $receipt = $cel->run($expr);

        static::assertTrue($receipt->result->getRawValue());
    }

    public function testDecimalComparisonWithUInt(): void
    {
        $cel = $this->createCel();
        $expr = $cel->parseString('decimal("10.5") > 5u');
        $receipt = $cel->run($expr);

        static::assertTrue($receipt->result->getRawValue());
    }

    public function testDecimalComparisonWithFloat(): void
    {
        $cel = $this->createCel();
        $expr = $cel->parseString('decimal("10.5") == 10.5');
        $receipt = $cel->run($expr);

        static::assertTrue($receipt->result->getRawValue());
    }

    public function testComplexDecimalExpression(): void
    {
        $cel = $this->createCel();
        $expr = $cel->parseString('(decimal("10.5") + decimal("5.5")) * decimal("2") - decimal("10")');
        $receipt = $cel->run($expr);

        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(DecimalNumber::class, $receipt->result->message);
        // (10.5 + 5.5) * 2 - 10 = 16 * 2 - 10 = 32 - 10 = 22
        static::assertTrue($receipt->result->message->getInner()->equals(new Decimal('22')));
    }

    public function testDecimalWithVariables(): void
    {
        $cel = $this->createCel();
        $expr = $cel->parseString('x + y');
        $receipt = $cel->run($expr, [
            'x' => new DecimalNumber(new Decimal('100.50')),
            'y' => new DecimalNumber(new Decimal('50.25')),
        ]);

        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(DecimalNumber::class, $receipt->result->message);
        static::assertTrue($receipt->result->message->getInner()->equals(new Decimal('150.75')));
    }

    public function testDecimalValueResolver(): void
    {
        $cel = $this->createCel();
        $expr = $cel->parseString('value * decimal("2")');
        $receipt = $cel->run($expr, [
            'value' => new DecimalNumber(new Decimal('25.5')),
        ]);

        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(DecimalNumber::class, $receipt->result->message);
        static::assertTrue($receipt->result->message->getInner()->equals(new Decimal('51')));
    }

    public function testDecimalPrecisionPreservation(): void
    {
        $cel = $this->createCel();
        $expr = $cel->parseString('decimal("1.23456789", 10)');
        $receipt = $cel->run($expr);

        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(DecimalNumber::class, $receipt->result->message);
        static::assertSame(10, $receipt->result->message->getInner()->precision());
    }

    public function testDecimalArithmeticWithAllTypes(): void
    {
        $cel = $this->createCel();

        // decimal + int
        $expr = $cel->parseString('decimal("10") + 5');
        $receipt = $cel->run($expr);
        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(DecimalNumber::class, $receipt->result->message);
        static::assertTrue($receipt->result->message->getInner()->equals(new Decimal('15')));

        // decimal + uint
        $expr = $cel->parseString('decimal("10") + 5u');
        $receipt = $cel->run($expr);
        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(DecimalNumber::class, $receipt->result->message);
        static::assertTrue($receipt->result->message->getInner()->equals(new Decimal('15')));

        // decimal + float
        $expr = $cel->parseString('decimal("10") + 5.5');
        $receipt = $cel->run($expr);
        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(MessageValue::class, $receipt->result);
        static::assertInstanceOf(DecimalNumber::class, $receipt->result->message);
        static::assertTrue($receipt->result->message->getInner()->equals(new Decimal('15.5')));
    }

    public function testDecimalMessageTypeRegistration(): void
    {
        $cel = $this->createCel();

        // Test that DecimalNumber is registered as a message type
        // by verifying we can use DecimalNumber instances in expressions
        $expr = $cel->parseString('x == y');
        $receipt = $cel->run($expr, [
            'x' => new DecimalNumber(new Decimal('123')),
            'y' => new DecimalNumber(new Decimal('123')),
        ]);

        static::assertTrue($receipt->result->getRawValue());
    }
}
