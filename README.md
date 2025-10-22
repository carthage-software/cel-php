# A Common Expression Language Implementation in PHP

[![continuous integration](https://github.com/carthage-software/cel-php/actions/workflows/ci.yml/badge.svg)](https://github.com/carthage-software/cel-php/actions/workflows/ci.yml)
[![Coverage Status](https://coveralls.io/repos/github/carthage-software/cel-php/badge.svg?branch=main)](https://coveralls.io/github/carthage-software/cel-php?branch=main)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fcarthage-software%2Fcel-php%2Fmain)](https://dashboard.stryker-mutator.io/reports/github.com/carthage-software/cel-php/main)
[![Total Downloads](https://poser.pugx.org/carthage-software/cel-php/d/total.svg)](https://packagist.org/packages/carthage-software/cel-php)
[![Latest Stable Version](https://poser.pugx.org/carthage-software/cel-php/v/stable.svg)](https://packagist.org/packages/carthage-software/cel-php)
[![License](https://poser.pugx.org/carthage-software/cel-php/license.svg)](https://packagist.org/packages/carthage-software/cel-php)

This repository contains a PHP implementation of the [Common Expression Language (CEL)](https://github.com/google/cel-spec).

## Table of Contents

- [Documentation](#documentation)
- [Example](#quick-example)
- [Specification Compliance](#specification-compliance)
- [License](#license)
- [Security Policy](#security-policy)
- [Code of Conduct](#code-of-conduct)
- [Contributing](#contributing)
- [Development](#development)
  - [Justfile Recipes](#justfile-recipes)
  - [Local Setup](#local-setup)

## Documentation

- [Getting Started](./docs/getting-started.md) - Installation, basic usage, and core concepts
- [Standard Extensions Reference](./docs/extensions.md) - Available functions and operators
- [Caching](./docs/caching.md) - Improve performance with caching
- [Custom Functions](./docs/custom-functions.md) - Extend CEL with your own functions
- [Custom Operators](./docs/custom-operators.md) - Add custom binary and unary operators
- [Value Resolvers](./docs/value-resolvers.md) - Support custom PHP types in CEL

## Quick Example

### Simple Usage

```php
use Cel;

// Simple expression evaluation
$result = Cel\evaluate('1 + 2');
echo $result->getRawValue(); // Output: 3

// With variables
$result = Cel\evaluate(
    'user.age >= 18',
    ['user' => ['age' => 25]]
);
echo $result->getRawValue(); // Output: true
```

### Full API Example

```php
use Cel;

const EXPRESSION = <<<CEL
    account.balance >= transaction.withdrawal
        || (account.overdraftProtection
        && account.overdraftLimit >= transaction.withdrawal - account.balance)
CEL;

// Create CEL instance
$cel = new Cel\CommonExpressionLanguage();

try {
    // Parse the expression
    $expression = $cel->parseString(EXPRESSION);

    // Evaluate with context
    $receipt = $cel->run($expression, [
        'account' => [
            'balance' => 500,
            'overdraftProtection' => true,
            'overdraftLimit' => 1000,
        ],
        'transaction' => [
            'withdrawal' => 700,
        ],
    ]);

    echo $receipt->result->getRawValue(); // Output: true

} catch (Cel\Parser\Exception\UnexpectedTokenException $e) {
    // Handle parsing errors
} catch (Cel\Exception\IncompatibleValueTypeException $e) {
    // Handle type errors
} catch (Cel\Exception\EvaluationException $e) {
    // Handle runtime errors
}
```

### Using Caching

```php
use Cel;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Psr16Cache;

// Create a cache instance
$cache = new Psr16Cache(new ApcuAdapter());

// Create CEL with caching enabled
$cel = Cel\CommonExpressionLanguage::cached($cache);

// Parsing and evaluation results will be cached automatically
$expression = $cel->parseString('1 + 2');
$receipt = $cel->run($expression);
```

## Specification Compliance

CEL-PHP is a **production-ready, spec-compliant** implementation of the Common Expression Language specification. All core language features, operators, macros, and standard library functions are fully implemented and tested.

### ✅ Implemented Features

- **Core Language**
  - All primitive types (int, uint, double, bool, string, bytes, null)
  - Lists and Maps with full indexing support
  - Duration and Timestamp types
  - Field selection and indexing
  - All operators (arithmetic, comparison, logical, membership)
  - Conditional expressions (`? :`)
  - Message construction
  - String/bytes literals with complete escape sequence support

- **Macros**
  - `has(e.f)` - Field presence checking
  - `e.all(x, p)` - Universal quantification
  - `e.exists(x, p)` - Existential quantification
  - `e.exists_one(x, p)` - Unique existence
  - `e.map(x, t)` - Transformation
  - `e.filter(x, p)` - Filtering

- **Standard Library**
  - Core functions (type conversions, size, type checking)
  - String functions (contains, split, trim, case conversion, etc.)
  - List functions (chunk, flatten, reverse, sort, etc.)
  - Math functions (min, max, sum, mean, median, etc.)
  - DateTime functions (timestamp, duration, accessors)
  - Decimal support for arbitrary precision (optional extension)

- **Runtime & Tooling**
  - Tree-walking interpreter with full error tracking
  - Expression optimization (constant folding, short-circuit evaluation, etc.)
  - Extension system for custom functions and operators
  - Value resolvers for custom PHP types
  - Parse and evaluation result caching (PSR-16 compatible)
  - Comprehensive exception handling with source span information

### 🎯 Production Ready for 1.0.0

This implementation is **ready for production use** and meets all requirements for a 1.0.0 release. The following are potential future enhancements that could improve performance or developer experience, but are **not required** for the core functionality:

- **Compile-time Type Checking**: Static analysis of expressions before runtime (nice-to-have for catching errors earlier)
- **Stack-based Interpreter**: Alternative execution engine for improved performance (current tree-walking interpreter is sufficient for most use cases)
- **Protocol Buffer Integration**: Native protobuf support (manual message construction works well)
- **Conformance Test Suite**: Official CEL conformance tests (current test suite of 1,080+ tests provides comprehensive coverage)

**Note**: Performance benchmarks show that complex expressions evaluate in ~0.001 seconds in production environments, which is acceptable for the vast majority of use cases.

## License

This project is licensed under the terms of the [LICENSE](LICENSE) file.

## Security Policy

For information on security vulnerabilities and how to report them, please refer to our [SECURITY.md](SECURITY.md).

## Code of Conduct

Please review our [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md) for expected behavior and guidelines for participation.

## Acknowledgments

This project was developed by [Carthage Software](https://carthage.software) and is fully funded by [Buhta](https://buhta.com).

We extend our sincere gratitude to **Buhta** for their generous support of open-source software. Their commitment to the PHP ecosystem makes projects like this possible.

## Contributing

We welcome contributions! Please see our [CONTRIBUTING.md](CONTRIBUTING.md) for details on how to get started.

## Development

This project uses `just` for task automation.

### Justfile Recipes

You can see all available commands by running `just --list`. Some common recipes include:

- `just install`: Installs project dependencies.
- `just test`: Runs the test suite.
- `just lint`: Runs linting checks.
- `just verify`: Runs all checks (tests, linting, etc.) to ensure code quality. **Always run `just verify` before pushing any changes.**

### Local Setup

To get started with local development, you'll need to install `just` and `typos`.

**Installing Just:**

If you have Rust and Cargo installed, you can install `just` via Cargo:

```bash
cargo install just
```

Alternatively, you can find other installation methods in the [Just documentation](https://github.com/casey/just#installation).

**Installing Typos:**

If you have Rust and Cargo installed, you can install `typos` via Cargo:

```bash
cargo install typos-cli
```

After installing `just` and `typos`, you can install the project dependencies and run verification checks:

```bash
just install
just verify
```
