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

- [License](#license)
- [Security Policy](#security-policy)
- [Code of Conduct](#code-of-conduct)
- [Contributing](#contributing)
- [Development](#development)
  - [Justfile Recipes](#justfile-recipes)
  - [Local Setup](#local-setup)

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
