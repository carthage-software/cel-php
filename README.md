# A Common Expression Language Implementation in PHP

[![continuous integration](https://github.com/carthage-software/cel-php/actions/workflows/ci.yml/badge.svg)](https://github.com/carthage-software/cel-php/actions/workflows/ci.yml)
[![Coverage Status](https://coveralls.io/repos/github/carthage-software/cel-php/badge.svg?branch=main)](https://coveralls.io/github/carthage-software/cel-php?branch=main)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fcarthage-software%2Fcel-php%2Fmain)](https://dashboard.stryker-mutator.io/reports/github.com/carthage-software/cel-php/main)
[![Total Downloads](https://poser.pugx.org/carthage-software/cel-php/d/total.svg)](https://packagist.org/packages/carthage-software/cel-php)
[![Latest Stable Version](https://poser.pugx.org/carthage-software/cel-php/v/stable.svg)](https://packagist.org/packages/carthage-software/cel-php)
[![License](https://poser.pugx.org/carthage-software/cel-php/license.svg)](https://packagist.org/packages/carthage-software/cel-php)

**This project is currently under heavy development and is not yet ready for production use.**

This repository contains a PHP implementation of the [Common Expression Language (CEL)](https://github.com/google/cel-spec).

## Table of Contents

- [TODO](#todo)
- [License](#license)
- [Security Policy](#security-policy)
- [Code of Conduct](#code-of-conduct)
- [Contributing](#contributing)
- [Development](#development)
  - [Justfile Recipes](#justfile-recipes)
  - [Local Setup](#local-setup)

## Example

```php
use Cel;
use Psl\IO;

const EXPRESSION = <<<CEL
    account.balance >= transaction.withdrawal
        || (account.overdraftProtection
        && account.overdraftLimit >= transaction.withdrawal - account.balance)
CEL;

try {
    $result = Cel\run(EXPRESSION, [
        'account' => [
            'balance' => 500,
            'overdraftProtection' => true,
            'overdraftLimit' => 1000,
        ],
        'transaction' => [
            'withdrawal' => 700,
        ],
    ]);
    
    IO\write_line('Result: %s(%s)', $result->getType(), var_export($result->getNativeValue(), true));
} catch (Cel\Parser\Exception\ExceptionInterface $exception) {
    // Parsing failed...
} catch (Cel\Runtime\Exception\IncompatibleValueTypeException $e) {
    // An environment variable has an incompatible type...
} catch (Cel\Runtime\Exception\RuntimeException $e) {
    // Some other runtime error...
}
```

## TODO

As mentioned earlier, this project is still under heavy development. Below is a non-exhaustive list of features and improvements that are planned:

- Language Features
  - [ ] Implement Macros
    - [x] `has(e.f)`
    - [x] `e.all(x, p)`
    - [x] `e.exists(x, p)`
    - [x] `e.exists_one(x, p)`
    - [ ] `e.map(x, t)` (for lists and maps)
    - [ ] `e.map(x, p, t)`
    - [ ] `e.filter(x, p)` (for lists and maps)
  - [ ] Support Optional Access
    - [ ] Implement `foo.?bar` syntax, similar to cel-go ( this is *NOT* part of the CEL spec ).
    - [ ] Support Message Creation
      - [ ] Implement `a.b.C{foo: 1}` syntax at runtime ( currently allowed by the parser, but results in a runtime error ).
      - [ ] Add a configuration layer to control which message types can be constructed at runtime.
        - For security reasons, we want to restrict which PHP classes can be instantiated via CEL expressions.
- Interpreter & Runtime
  - [x] Tree-Walking Interpreter (Implemented)
  - [ ] Stack-Based Interpreter
    - [ ] Design opcode set
    - [ ] Implement compiler (AST -> Bytecode)
    - [ ] Implement stack-based VM
- Performance
  - [ ] Caching Strategy
    - [ ] Investigate and implement caching for parsed expressions (AST).
    - [ ] Explore potential for caching evaluation results for idempotent functions.
- API & Quality
  - [ ] API Improvements
    - [ ] General review and cleanup of the public-facing API.
  - [ ] Testing & Coverage
    - [ ] Achieve 100% test coverage.
    - [ ] Achieve 100% mutation score.
    - [x] Achieve 100% static analysis type coverage.

## License

This project is licensed under the terms of the [LICENSE](LICENSE) file.

## Security Policy

For information on security vulnerabilities and how to report them, please refer to our [SECURITY.md](SECURITY.md).

## Code of Conduct

Please review our [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md) for expected behavior and guidelines for participation.

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
