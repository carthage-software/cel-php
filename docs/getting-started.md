# Getting Started with CEL-PHP

This guide will help you get started with CEL-PHP, a PHP implementation of Google's Common Expression Language.

## Installation

Install CEL-PHP via Composer:

```bash
composer require carthage-software/cel-php
```

## Basic Usage

The simplest way to use CEL-PHP is through the convenience function:

```php
use Cel;

// Simple expression evaluation
$result = Cel\evaluate('1 + 2');
echo $result->getRawValue(); // Output: 3
```

### Evaluating with Variables

You can provide variables to your expressions:

```php
use Cel;

$result = Cel\evaluate('user.age >= 18', [
    'user' => [
        'age' => 25,
        'name' => 'John',
    ],
]);

echo $result->getRawValue(); // Output: true
```

### Using the Full API

For more control, you can use the full API:

```php
use Cel;

// Create a CEL instance
$cel = new Cel\CommonExpressionLanguage();

// Parse the expression
$expression = $cel->parseString('account.balance >= transaction.amount');

// Evaluate with context
$receipt = $cel->run($expression, [
    'account' => ['balance' => 1000],
    'transaction' => ['amount' => 500],
]);

echo $receipt->result->getRawValue(); // Output: true
```

## Working with Different Types

CEL supports various data types:

### Numbers

```php
// Integers
$result = Cel\evaluate('42');

// Floating point
$result = Cel\evaluate('3.14');

// Unsigned integers
$result = Cel\evaluate('42u');
```

### Strings

```php
$result = Cel\evaluate('"Hello, " + name', ['name' => 'World']);
echo $result->getRawValue(); // Output: Hello, World
```

### Booleans

```php
$result = Cel\evaluate('true && false');
echo $result->getRawValue(); // Output: false
```

### Lists

```php
$result = Cel\evaluate('[1, 2, 3].size()');
echo $result->getRawValue(); // Output: 3

$result = Cel\evaluate('items[0]', ['items' => ['a', 'b', 'c']]);
echo $result->getRawValue(); // Output: a
```

### Maps

```php
$result = Cel\evaluate('{"key": "value"}["key"]');
echo $result->getRawValue(); // Output: value

$result = Cel\evaluate('user.name', ['user' => ['name' => 'Alice']]);
echo $result->getRawValue(); // Output: Alice
```

### Duration and Timestamp

```php
// Duration
$result = Cel\evaluate('duration("1h30m")');

// Timestamp
$result = Cel\evaluate('timestamp("2024-01-01T00:00:00Z")');
```

## Macros

CEL-PHP supports powerful macros for working with collections:

### has() - Check field existence

```php
$result = Cel\evaluate(
    'has(user.email)',
    ['user' => ['name' => 'John']]
);
echo $result->getRawValue(); // Output: false
```

### all() - Check if all elements satisfy a condition

```php
$result = Cel\evaluate(
    'numbers.all(n, n > 0)',
    ['numbers' => [1, 2, 3, 4, 5]]
);
echo $result->getRawValue(); // Output: true
```

### exists() - Check if any element satisfies a condition

```php
$result = Cel\evaluate(
    'numbers.exists(n, n > 10)',
    ['numbers' => [5, 10, 15, 20]]
);
echo $result->getRawValue(); // Output: true
```

### exists_one() - Check if exactly one element satisfies a condition

```php
$result = Cel\evaluate(
    'numbers.exists_one(n, n == 10)',
    ['numbers' => [5, 10, 15, 20]]
);
echo $result->getRawValue(); // Output: true
```

### filter() - Filter elements

```php
$result = Cel\evaluate(
    'numbers.filter(n, n > 10)',
    ['numbers' => [5, 10, 15, 20, 25]]
);
// Output: [15, 20, 25]
```

### map() - Transform elements

```php
$result = Cel\evaluate(
    'numbers.map(n, n * 2)',
    ['numbers' => [1, 2, 3, 4, 5]]
);
// Output: [2, 4, 6, 8, 10]
```

## Error Handling

CEL-PHP throws specific exceptions for different error conditions:

```php
use Cel;
use Cel\Parser\Exception\UnexpectedTokenException;
use Cel\Parser\Exception\UnexpectedEndOfFileException;
use Cel\Exception\EvaluationException;
use Cel\Exception\IncompatibleValueTypeException;

try {
    $result = Cel\evaluate('invalid syntax here');
} catch (UnexpectedTokenException | UnexpectedEndOfFileException $e) {
    // Handle parsing errors
    echo "Parse error: " . $e->getMessage();
} catch (IncompatibleValueTypeException $e) {
    // Handle type conversion errors
    echo "Type error: " . $e->getMessage();
} catch (EvaluationException $e) {
    // Handle runtime evaluation errors
    echo "Evaluation error: " . $e->getMessage();
}
```

## Configuration

You can customize the CEL runtime behavior:

```php
use Cel;

// Create a custom configuration
$configuration = new Cel\Runtime\Configuration(
    enableMacros: true,                    // Enable macros (default: true)
    enableStandardExtensions: true,        // Enable standard extensions (default: true)
    allowedMessageClasses: [],             // Restrict message types for security
    messageClassAliases: [],               // Define custom type aliases
    enforceMessageClassAliases: false,     // Enforce alias usage
);

// Create runtime with custom configuration
$runtime = new Cel\Runtime\Runtime(configuration: $configuration);
$cel = new Cel\CommonExpressionLanguage(runtime: $runtime);

// Use it
$expression = $cel->parseString('1 + 2');
$receipt = $cel->run($expression);
```

## Next Steps

- Learn about [Extensions](./extensions.md) - Available functions and operators
- Learn about [Caching](./caching.md) - Improve performance with caching
- Learn about [Custom Functions](./custom-functions.md) - Extend CEL with your own functions
- Learn about [Custom Operators](./custom-operators.md) - Add custom operators
- Learn about [Value Resolvers](./value-resolvers.md) - Support custom types
