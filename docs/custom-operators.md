# Creating Custom Operators

CEL-PHP allows you to extend operators with custom type overloads through extensions. This guide shows you how to create binary and unary operator handlers.

## Overview

Operator overloads define how operators work with different value types. Each overload consists of:

1. **Handler** - Implements the logic for specific type combinations
2. **Operator Class** - Registers handlers for an operator
3. **Extension** - Makes the operator available in the runtime

## Binary Operators

Binary operators work with two operands (e.g., `+`, `-`, `==`, `<`).

### Creating a Binary Operator

```php
<?php

namespace App\Cel\Operator\Handler;

use Cel\Operator\BinaryOperatorOverloadHandlerInterface;
use Cel\Syntax\Binary\BinaryExpression;
use Cel\Util\OperandUtil;
use Cel\Value\{StringValue, MessageValue, Value};

final readonly class StringPlusMessageHandler implements BinaryOperatorOverloadHandlerInterface
{
    public function __invoke(BinaryExpression $expression, Value $left, Value $right): Value
    {
        $left = OperandUtil::assertLeft($left, StringValue::class);
        $right = OperandUtil::assertRight($right, MessageValue::class);

        $result = $left->value . ' ' . $right->message::class;
        return new StringValue($result);
    }
}
```

### Registering the Operator

```php
<?php

namespace App\Cel\Operator;

use Cel\Operator\BinaryOperatorOverloadInterface;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Cel\Value\ValueKind;

final readonly class AdditionOperator implements BinaryOperatorOverloadInterface
{
    public function getOperator(): BinaryOperatorKind
    {
        return BinaryOperatorKind::Plus;
    }

    public function getOverloads(): iterable
    {
        yield [ValueKind::String, ValueKind::Message] => new StringPlusMessageHandler();
        // Add more overloads for other type combinations
    }
}
```

### Creating an Extension

```php
<?php

namespace App\Cel\Extension;

use Cel\Extension\ExtensionInterface;

final class CustomOperatorExtension implements ExtensionInterface
{
    public function getBinaryOperatorOverloads(): array
    {
        return [new AdditionOperator()];
    }

    public function getFunctions(): array { return []; }
    public function getUnaryOperatorOverloads(): array { return []; }
    public function getMessageTypes(): array { return []; }
    public function getValueResolvers(): array { return []; }
}
```

### Usage

```php
$runtime = new Cel\Runtime\Runtime();
$runtime->register(new CustomOperatorExtension());

$cel = new Cel\CommonExpressionLanguage(runtime: $runtime);
$expression = $cel->parseString('"Prefix:" + message');
$receipt = $cel->run($expression, ['message' => new YourMessage()]);
```

## Unary Operators

Unary operators work with a single operand (e.g., `-`, `!`).

### Creating a Unary Operator

```php
<?php

namespace App\Cel\Operator\Handler;

use Cel\Operator\UnaryOperatorOverloadHandlerInterface;
use Cel\Syntax\Unary\UnaryExpression;
use Cel\Util\OperandUtil;
use Cel\Value\{FloatValue, Value};

final readonly class NegateFloatHandler implements UnaryOperatorOverloadHandlerInterface
{
    public function __invoke(UnaryExpression $expression, Value $operand): Value
    {
        $operand = OperandUtil::assert($operand, FloatValue::class);
        return new FloatValue(-$operand->value);
    }
}
```

### Registering the Operator

```php
<?php

namespace App\Cel\Operator;

use Cel\Operator\UnaryOperatorOverloadInterface;
use Cel\Syntax\Unary\UnaryOperatorKind;
use Cel\Value\ValueKind;

final readonly class NegationOperator implements UnaryOperatorOverloadInterface
{
    public function getOperator(): UnaryOperatorKind
    {
        return UnaryOperatorKind::Negate;
    }

    public function getOverloads(): iterable
    {
        yield ValueKind::Float => new NegateFloatHandler();
    }
}
```

Register it the same way as binary operators through an extension.

## Multiple Type Combinations

Define multiple handlers for the same operator to support different type combinations:

```php
public function getOverloads(): iterable
{
    yield [ValueKind::String, ValueKind::String] => new StringConcatHandler();
    yield [ValueKind::String, ValueKind::Integer] => new StringIntHandler();
    yield [ValueKind::List, ValueKind::List] => new ListMergeHandler();
}
```

## Reference

### Binary Operator Kinds

- `Plus`, `Minus`, `Multiply`, `Divide`, `Modulo` - Arithmetic
- `Equal`, `NotEqual` - Equality
- `LessThan`, `LessThanOrEqual`, `GreaterThan`, `GreaterThanOrEqual` - Comparison
- `LogicalAnd`, `LogicalOr` - Logical
- `In` - Membership

### Unary Operator Kinds

- `Negate` - Negation (`-`)
- `LogicalNot` - Logical NOT (`!`)

### Value Kinds

- `Null`, `Boolean`, `Integer`, `UnsignedInteger`, `Float`
- `String`, `Bytes`
- `List`, `Map`, `Message`
- `Duration`, `Timestamp`

## Error Handling

Always validate operands using `OperandUtil` methods:

```php
public function __invoke(BinaryExpression $expression, Value $left, Value $right): Value
{
    $left = OperandUtil::assertLeft($left, IntegerValue::class);
    $right = OperandUtil::assertRight($right, IntegerValue::class);

    if ($right->value === 0) {
        throw new EvaluationException('Division by zero', $expression->getSpan());
    }

    return new IntegerValue(intdiv($left->value, $right->value));
}
```

## Best Practices

1. **Type Safety** - Always validate operands with `OperandUtil::assert*()` methods
2. **Immutability** - Make handlers `readonly` when possible
3. **Error Messages** - Include expression span for better debugging
4. **Performance** - Keep handler logic simple and fast
5. **Testing** - Test all type combinations and edge cases

## See Also

- [Value Resolvers](./value-resolvers.md) - Support custom types
- [Custom Functions](./custom-functions.md) - Extend with custom functions
