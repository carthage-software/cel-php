# Equality and Comparison

CEL-PHP implements [CEL proposal 210 (Numeric Comparisons and Null Equality)](https://github.com/cel-expr/cel-spec/wiki/proposal-210),
so that dynamically typed and nullable data is easy to use with `==`, `!=`, and the ordering
operators. These semantics are always on.

## Table of Contents

- [Total Equality](#total-equality)
- [Numeric Comparison Across Types](#numeric-comparison-across-types)
- [Null Equality](#null-equality)
- [NaN](#nan)
- [Set Membership and Map Access](#set-membership-and-map-access)
- [Notes and Limitations](#notes-and-limitations)

## Total Equality

`==` and `!=` never raise an error on a type mismatch. Values of incompatible types are simply
unequal:

```php
Cel\evaluate('1 == "1"');   // false
Cel\evaluate('true == 1');  // false
Cel\evaluate('{} == 1');    // false
Cel\evaluate('1 != "x"');   // true
```

Numbers are the exception: `int`, `uint`, and `double` are compared as though they lie on a single
number line (see below), including when nested inside lists and maps:

```php
Cel\evaluate('1 == 1.0');                 // true
Cel\evaluate('[1, 2] == [1.0, 2.0]');     // true
Cel\evaluate('{"a": 1} == {"a": 1.0}');   // true
```

## Numeric Comparison Across Types

The ordering operators (`<`, `<=`, `>`, `>=`) and equality work across every combination of `int`,
`uint`, and `double`:

```php
Cel\evaluate('1 < 1.5');     // true
Cel\evaluate('1u > 2');      // false
Cel\evaluate('-1 < 1u');     // true
Cel\evaluate('2.5 > 2');     // true
Cel\evaluate('1 == 1u');     // true
```

Comparisons are precision-correct: integers are compared exactly, and comparisons involving a
`double` are range-checked so that very large `int`/`uint` values are ordered correctly against
doubles outside the double's integer range.

## Null Equality

`null` may be compared to any value. It is equal only to `null`:

```php
Cel\evaluate('null == null');   // true
Cel\evaluate('0 == null');      // false
Cel\evaluate('[] != null');     // true
Cel\evaluate('null in [1, 2]'); // false
```

## NaN

`NaN` (which enters an expression through a variable, since CEL has no `NaN` literal) is equal to
nothing, including itself, and cannot be ordered:

```php
Cel\evaluate('x == x', ['x' => NAN]);   // false
Cel\evaluate('x != 1.0', ['x' => NAN]); // true
Cel\evaluate('x < 1.0', ['x' => NAN]);  // throws: NaN values cannot be ordered
```

## Set Membership and Map Access

Heterogeneous numeric equality extends to the `in` operator and to map indexing. An integer-valued
numeric key (an `int`, a `uint`, or an integral `double`) matches an integer map key regardless of
its original numeric type:

```php
Cel\evaluate('1 in [1.0, 2.5]');    // true
Cel\evaluate('1.0 in {1: "a"}');    // true
Cel\evaluate('{1: "hello"}[1.0]');  // "hello"
Cel\evaluate('{1u: true}[1.0]');    // true
```

## Notes and Limitations

- **JSON type.** Proposal 210 also introduces a distinct `json` type used to improve *type-checker*
  inferences. CEL-PHP evaluates expressions at runtime without a separate type-check phase, so the
  `json` type does not apply; the runtime numeric/null behavior above is what it provides.
- **Map keys.** CEL-PHP maps are backed by native PHP arrays, which cannot distinguish `int` from
  `uint` keys or hold `bool` keys, and cannot hold `uint` keys larger than `PHP_INT_MAX`. Integer,
  unsigned-integer, and string map keys are supported; heterogeneous numeric lookup works for
  integer-valued keys.
