# Value Resolvers

Value resolvers enable CEL-PHP to work with custom PHP types by converting them into CEL values.

## Overview

Value resolvers convert raw PHP values into CEL `Value` instances, allowing you to:

- Support custom PHP objects in CEL expressions
- Integrate third-party libraries (BCMath, Decimal, Money, etc.)
- Define custom conversion logic for specific types
- Enable seamless interoperability between PHP and CEL types

## The Interface

```php
interface ValueResolverInterface
{
    /**
     * Checks if this resolver can handle the given raw value.
     */
    public function canResolve(mixed $value): bool;

    /**
     * Converts a raw PHP value into a CEL Value instance.
     *
     * @throws IncompatibleValueTypeException If the value cannot be converted
     */
    public function resolve(mixed $value): Value;
}
```

## Simple Value Resolver

For basic type conversions, return a standard CEL value:

```php
<?php

namespace App\Cel\Resolver;

use Cel\Value\Resolver\ValueResolverInterface;
use Cel\Value\{TimestampValue, Value};
use DateTime;

final readonly class DateTimeValueResolver implements ValueResolverInterface
{
    public function canResolve(mixed $value): bool
    {
        return $value instanceof DateTime;
    }

    public function resolve(mixed $value): Value
    {
        assert($value instanceof DateTime);
        return new TimestampValue($value->getTimestamp());
    }
}
```

### Registration

```php
<?php

namespace App\Cel\Extension;

use Cel\Extension\ExtensionInterface;

final class DateTimeExtension implements ExtensionInterface
{
    public function getValueResolvers(): array
    {
        return [new DateTimeValueResolver()];
    }

    public function getFunctions(): array { return []; }
    public function getBinaryOperatorOverloads(): array { return []; }
    public function getUnaryOperatorOverloads(): array { return []; }
    public function getMessageTypes(): array { return []; }
}
```

### Usage

```php
$runtime = new Cel\Runtime\Runtime();
$runtime->register(new DateTimeExtension());

$cel = new Cel\CommonExpressionLanguage(runtime: $runtime);
$expression = $cel->parseString('event.timestamp > deadline');
$receipt = $cel->run($expression, [
    'event' => ['timestamp' => new DateTime('2024-06-01')],
    'deadline' => new DateTime('2024-05-01'),
]);
```

## Message Value Resolver

For complex types that need custom operators, wrap them as Message values:

```php
<?php

namespace App\Cel;

use App\Money\Money;
use Cel\Message\MessageInterface;
use Cel\Value\{MessageValue, Value};
use Cel\Value\Resolver\ValueResolverInterface;

// Message wrapper
final readonly class MoneyMessage implements MessageInterface
{
    public function __construct(private Money $money) {}

    public function getMoney(): Money { return $this->money; }

    public function toCelValue(): Value
    {
        return new MessageValue($this, [
            'amount' => $this->money->getAmount(),
            'currency' => $this->money->getCurrency(),
        ]);
    }

    public static function fromCelFields(array $fields): static
    {
        return new self(new Money(
            amount: $fields['amount'],
            currency: $fields['currency'],
        ));
    }
}

// Value resolver
final readonly class MoneyValueResolver implements ValueResolverInterface
{
    public function canResolve(mixed $value): bool
    {
        return $value instanceof Money;
    }

    public function resolve(mixed $value): Value
    {
        assert($value instanceof Money);
        return new MessageValue(new MoneyMessage($value), [
            'amount' => $value->getAmount(),
            'currency' => $value->getCurrency(),
        ]);
    }
}
```

### Extension with Custom Operators

```php
<?php

namespace App\Cel\Extension;

use Cel\Extension\ExtensionInterface;

final class MoneyExtension implements ExtensionInterface
{
    public function getValueResolvers(): array
    {
        return [new MoneyValueResolver()];
    }

    public function getBinaryOperatorOverloads(): array
    {
        return [
            new MoneyAdditionOperator(),
            new MoneyComparisonOperator(),
        ];
    }

    public function getMessageTypes(): array
    {
        return [MoneyMessage::class => ['Money']];
    }

    public function getFunctions(): array { return []; }
    public function getUnaryOperatorOverloads(): array { return []; }
}
```

## Type Mapping

| PHP Type | CEL Value | Via Resolver |
|----------|-----------|--------------|
| `null` | `NullValue` | No (built-in) |
| `bool` | `BooleanValue` | No (built-in) |
| `int` | `IntegerValue` | No (built-in) |
| `float` | `FloatValue` | No (built-in) |
| `string` | `StringValue` | No (built-in) |
| `array` (list) | `ListValue` | No (built-in) |
| `array` (map) | `MapValue` | No (built-in) |
| Custom object | `MessageValue` | Yes |
| `DateTime` | `TimestampValue` | Yes |
| `DateInterval` | `DurationValue` | Yes |

## Resolver Priority

Value resolvers are checked in registration order:

1. Extension resolvers (in registration order)
2. Default resolver (handles built-in types via `Value::from()`)

The first resolver where `canResolve()` returns `true` will be used.

```php
public function getValueResolvers(): array
{
    return [
        new SpecialDateTimeResolver(),  // Checked first
        new GeneralDateTimeResolver(),  // Checked second
    ];
}
```

## Error Handling

Validate inputs and provide clear error messages:

```php
use Cel\Exception\IncompatibleValueTypeException;

public function resolve(mixed $value): Value
{
    assert($value instanceof Money);

    if ($value->getAmount() < 0) {
        throw new IncompatibleValueTypeException('Money amount cannot be negative');
    }

    if (strlen($value->getCurrency()) !== 3) {
        throw new IncompatibleValueTypeException('Currency must be 3-letter ISO code');
    }

    return new MessageValue(new MoneyMessage($value), [
        'amount' => $value->getAmount(),
        'currency' => $value->getCurrency(),
    ]);
}
```

## Working with Collections

Resolvers can handle nested structures - child values are resolved automatically:

```php
final readonly class UserCollectionResolver implements ValueResolverInterface
{
    public function canResolve(mixed $value): bool
    {
        return $value instanceof UserCollection;
    }

    public function resolve(mixed $value): Value
    {
        assert($value instanceof UserCollection);

        $items = [];
        foreach ($value as $user) {
            $items[] = Value::from($user);  // Recursively resolved
        }

        return new ListValue($items);
    }
}
```

## Best Practices

1. **Specific Type Checks** - Make `canResolve()` as specific as possible to avoid conflicts
2. **Assert Types** - Always assert the type in `resolve()` for type safety
3. **Error Messages** - Provide clear error messages when conversion fails
4. **Immutability** - Make resolvers `readonly` when possible
5. **Performance** - Keep resolution logic fast - it's called for every value
6. **Testing** - Test edge cases, null values, and invalid inputs

## See Also

- [Custom Operators](./custom-operators.md) - Define operators for your custom types
- [Custom Functions](./custom-functions.md) - Create functions that work with your types
