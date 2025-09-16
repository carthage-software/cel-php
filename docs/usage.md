# Getting Started with CEL-PHP

This guide will walk you through the basics of using the `cel-php` library to evaluate Common Expression Language (CEL) expressions in your PHP applications.

## Basic Evaluation

The simplest way to evaluate an expression is to use the `Cel\run()` function. It takes a string containing the CEL expression and returns the result.

```php
<?php

require 'vendor/autoload.php';

use function Cel\run;

$result = run('1 + 1');

var_dump($result->getNativeValue()); // int(2)
```

The `run()` function returns a `Cel\Runtime\Value\Value` object. To get the raw PHP value, you can call the `getNativeValue()` method.

## Using Variables

You can pass variables to your expression by providing an associative array as the second argument to `run()`.

```php
<?php

require 'vendor/autoload.php';

use function Cel\run;

$result = run('request.amount < 1000', [
    'request' => ['amount' => 750]
]);

var_dump($result->getNativeValue()); // bool(true)
```

The library automatically converts standard PHP types (arrays, strings, integers, booleans, etc.) into their corresponding CEL types.

## Configuration

The `run()` function accepts an optional `Cel\Runtime\Configuration` object as its third argument. This allows you to control various runtime features, such as enabling or disabling extensions and macros.

```php
<?php

require 'vendor/autoload.php';

use function Cel\run;
use Cel\Runtime\Configuration;

// Disable the Math extension
$config = new Configuration(enableMathExtension: false);

// This expression will now fail because the `max()` function is not available
run('max(1, 2)', [], $config);
```

The `Configuration` class has the following public properties:

- `enableMacros` (`bool`): Enables or disables macros like `has()`, `all()`, and `exists()`. Defaults to `true`.
- `enableCoreExtension` (`bool`): Enables the [Core extension](./extensions.md#core-extension). Defaults to `true`.
- `enableDateTimeExtension` (`bool`): Enables the [DateTime extension](./extensions.md#datetime-extension). Defaults to `true`.
- `enableMathExtension` (`bool`): Enables the [Math extension](./extensions.md#math-extension). Defaults to `true`.
- `enableStringExtension` (`bool`): Enables the [String extension](./extensions.md#string-extension). Defaults to `true`.
- `enableListExtension` (`bool`): Enables the [List extension](./extensions.md#list-extension). Defaults to `true`.
- `allowedMessageClasses` (`list<class-string<MessageInterface>>`): A security allowlist of message classes that can be constructed within an expression. By default, no message construction is allowed.

## Handling Exceptions

If an expression is syntactically incorrect or a runtime error occurs, the library will throw an exception. You can catch the base `Cel\Exception\ExceptionInterface` to handle any error from the library.

### Parse Errors

Parse errors occur when the expression string is not valid CEL syntax. These errors result in a `Cel\Parser\Exception\ExceptionInterface`.

```php
<?php

require 'vendor/autoload.php';

use function Cel\run;
use Cel\Parser\Exception\ExceptionInterface as ParserException;

try {
    run('1 + '); // Malformed expression
} catch (ParserException $e) {
    echo "Parse Error: " . $e->getMessage();
}
```

### Runtime Errors

Runtime errors occur during evaluation, such as a missing variable, a function call with incorrect argument types, or an unsupported operation. These errors result in a `Cel\Runtime\Exception\EvaluationException`.

```php
<?php

require 'vendor/autoload.php';

use function Cel\run;
use Cel\Runtime\Exception\EvaluationException;

try {
    // "name" is not defined in the environment
    run('user.name == "Alice"');
} catch (EvaluationException $e) {
    echo "EvaluationException: " . $e->getMessage();
}
```
