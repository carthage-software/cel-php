# Creating Custom Functions

CEL-PHP allows you to extend the language with custom functions through extensions.

## Basic Function Creation

A custom function requires three components:

1. **Function Overload Handler** - Implements the function logic
2. **Function Class** - Implements `FunctionInterface` and provides overloads
3. **Extension** - Registers the function

### Example: Simple Custom Function

Let's create a `greet(name)` function:

```php
<?php

namespace App\Cel\Function\Handler;

use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Util\ArgumentsUtil;
use Cel\Syntax\Member\CallExpression;
use Cel\Value\StringValue;

final readonly class GreetHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @throws InternalException If argument extraction fails
     */
    public function __invoke(CallExpression $call, array $arguments): StringValue
    {
        $name = ArgumentsUtil::get($arguments, 0, StringValue::class);

        return new StringValue('Hello, ' . $name->value . '!');
    }
}
```

Now create the function class:

```php
<?php

namespace App\Cel\Function;

use App\Cel\Function\Handler\GreetHandler;
use Cel\Function\FunctionInterface;
use Cel\Value\ValueKind;

final readonly class GreetFunction implements FunctionInterface
{
    public function getName(): string
    {
        return 'greet';
    }

    public function isIdempotent(): bool
    {
        return true;
    }

    public function getOverloads(): iterable
    {
        yield [ValueKind::String] => new GreetHandler();
    }
}
```

Create an extension to register your function:

```php
<?php

namespace App\Cel\Extension;

use App\Cel\Function\GreetFunction;
use Cel\Extension\ExtensionInterface;

final class GreetingExtension implements ExtensionInterface
{
    public function getFunctions(): array
    {
        return [
            new GreetFunction(),
        ];
    }

    public function getBinaryOperatorOverloads(): array
    {
        return [];
    }

    public function getUnaryOperatorOverloads(): array
    {
        return [];
    }

    public function getMessageTypes(): array
    {
        return [];
    }

    public function getValueResolvers(): array
    {
        return [];
    }
}
```

## Using Your Custom Function

```php
use Cel;
use App\Cel\Extension\GreetingExtension;

$runtime = new Cel\Runtime\Runtime();
$runtime->register(new GreetingExtension());

$cel = new Cel\CommonExpressionLanguage(runtime: $runtime);

$expression = $cel->parseString('greet("World")');
$receipt = $cel->run($expression);

echo $receipt->result->getRawValue(); // Output: Hello, World!
```

## Function Overloading

CEL supports function overloading - multiple implementations for different argument types.

```php
// Handler for greet(string)
final readonly class GreetStringHandler implements FunctionOverloadHandlerInterface
{
    public function __invoke(CallExpression $call, array $arguments): StringValue
    {
        $name = ArgumentsUtil::get($arguments, 0, StringValue::class);

        return new StringValue('Hello, ' . $name->value . '!');
    }
}

// Handler for greet(list<string>)
final readonly class GreetListHandler implements FunctionOverloadHandlerInterface
{
    public function __invoke(CallExpression $call, array $arguments): StringValue
    {
        $names = ArgumentsUtil::get($arguments, 0, ListValue::class);

        $nameStrings = [];
        foreach ($names->items as $item) {
            if (!$item instanceof StringValue) {
                throw new EvaluationException(
                    'All list items must be strings',
                    $call->getSpan()
                );
            }
            $nameStrings[] = $item->value;
        }

        return new StringValue('Hello, ' . implode(', ', $nameStrings) . '!');
    }
}

// Function with multiple overloads
final readonly class GreetFunction implements FunctionInterface
{
    public function getName(): string
    {
        return 'greet';
    }

    public function isIdempotent(): bool
    {
        return true;
    }

    public function getOverloads(): iterable
    {
        // greet(string) -> string
        yield [ValueKind::String] => new GreetStringHandler();

        // greet(list) -> string
        yield [ValueKind::List] => new GreetListHandler();
    }
}
```

Usage:

```php
$result = Cel\evaluate('greet("Alice")', [], $configuration);
// Output: Hello, Alice!

$result = Cel\evaluate('greet(["Alice", "Bob", "Charlie"])', [], $configuration);
// Output: Hello, Alice, Bob, Charlie!
```

## Functions with Multiple Arguments

```php
final readonly class PowerHandler implements FunctionOverloadHandlerInterface
{
    public function __invoke(CallExpression $call, array $arguments): IntegerValue
    {
        $base = ArgumentsUtil::get($arguments, 0, IntegerValue::class);
        $exponent = ArgumentsUtil::get($arguments, 1, IntegerValue::class);

        return new IntegerValue((int) pow($base->value, $exponent->value));
    }
}

final readonly class PowerFunction implements FunctionInterface
{
    public function getName(): string
    {
        return 'power';
    }

    public function isIdempotent(): bool
    {
        return true;
    }

    public function getOverloads(): iterable
    {
        yield [ValueKind::Integer, ValueKind::Integer] => new PowerHandler();
    }
}
```

Usage:

```php
$result = Cel\evaluate('power(2, 10)');
echo $result->getRawValue(); // Output: 1024
```

## Error Handling

Always handle errors appropriately:

```php
use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;

final readonly class DivideHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @throws InternalException If argument extraction fails
     * @throws EvaluationException If division by zero
     */
    public function __invoke(CallExpression $call, array $arguments): FloatValue
    {
        $dividend = ArgumentsUtil::get($arguments, 0, IntegerValue::class);
        $divisor = ArgumentsUtil::get($arguments, 1, IntegerValue::class);

        if ($divisor->value === 0) {
            throw new EvaluationException(
                'Division by zero',
                $call->getSpan()
            );
        }

        return new FloatValue($dividend->value / $divisor->value);
    }
}
```

## Working with External Libraries

Wrap exceptions from external libraries:

```php
use Psl\Exception\ExceptionInterface as PslException;

final readonly class JsonParseHandler implements FunctionOverloadHandlerInterface
{
    public function __invoke(CallExpression $call, array $arguments): Value
    {
        $json = ArgumentsUtil::get($arguments, 0, StringValue::class);

        try {
            $decoded = Psl\Json\decode($json->value);
            return Value::fromNativeValue($decoded);
        } catch (PslException $e) {
            throw new EvaluationException(
                'JSON parsing failed: ' . $e->getMessage(),
                $call->getSpan(),
                $e
            );
        }
    }
}
```

## Best Practices

1. **Type Safety**: Always use `ArgumentsUtil::get()` to extract and validate arguments
2. **Error Messages**: Provide clear error messages with context
3. **Exception Handling**: Wrap external exceptions in CEL exceptions
4. **Documentation**: Document your handlers with PHPDoc
5. **Immutability**: Make handlers `readonly` when possible
6. **Idempotency**: Set `isIdempotent()` to `true` only if function has no side effects, allows caching and optimization

## Next Steps

- Learn about [Custom Operators](./custom-operators.md)
- Learn about [Value Resolvers](./value-resolvers.md)
- Explore [Standard Extensions](./extensions.md) for examples
