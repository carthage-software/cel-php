# Optional Values

CEL-PHP implements [CEL proposal 246 (Optional Values)](https://github.com/cel-expr/cel-spec/wiki/proposal-246).
Optionals make it possible to express _"a value that may or may not be present"_: conditionally
provided variables, field selections that may be absent, and fields that are only set when a value
exists, without deeply nested `has()` / ternary expressions.

An `optional(T)` either holds a value (`optional.of(x)`) or is empty (`optional.none()`). Optional
support is enabled by default.

## Table of Contents

- [Creating Optionals](#creating-optionals)
- [Inspecting Optionals](#inspecting-optionals)
- [Optional Field Selection and Indexing](#optional-field-selection-and-indexing)
- [Viral Propagation](#viral-propagation)
- [Optional Construction](#optional-construction)
- [Combining Optionals](#combining-optionals)
- [Transforming Optionals](#transforming-optionals)
- [List Helpers](#list-helpers)
- [Equality](#equality)
- [PHP API](#php-api)

## Creating Optionals

| Expression                   | Result                                                                          |
| ---------------------------- | ------------------------------------------------------------------------------- |
| `optional.of(v)`             | An optional holding `v` (any value, including `null`).                          |
| `optional.ofNonZeroValue(v)` | `optional.of(v)` unless `v` is a _zero value_, in which case `optional.none()`. |
| `optional.none()`            | An empty optional.                                                              |

A _zero value_ is the default empty value for a type: `null`, `false`, numeric zero (`0`, `0u`,
`0.0`), the empty string `""`, empty bytes `b""`, the empty list `[]`, the empty map `{}`, and a
zero `duration`. Timestamps and optionals are never zero values. Messages are never zero values
unless the underlying message implements `Cel\Message\ZeroValueInterface` and reports itself as zero
(the bundled `decimal` type does this, so `decimal("0")` is a zero value).

```php
Cel\evaluate('optional.of(42).value()');                  // 42
Cel\evaluate('optional.ofNonZeroValue("").hasValue()');   // false
Cel\evaluate('optional.ofNonZeroValue("hi").hasValue()'); // true
```

## Inspecting Optionals

| Method           | Result                                                                     |
| ---------------- | -------------------------------------------------------------------------- |
| `opt.hasValue()` | `true` if the optional holds a value.                                      |
| `opt.value()`    | The contained value, or an error (`optional.none() dereference`) if empty. |

```php
Cel\evaluate('optional.of(1).hasValue()'); // true
Cel\evaluate('optional.none().hasValue()'); // false
Cel\evaluate('optional.of(1).value()');     // 1
```

## Optional Field Selection and Indexing

Prefix a selection or index with `?` to get an optional instead of an error when the field, key, or
index is absent.

| Syntax         | Meaning                                                      |
| -------------- | ------------------------------------------------------------ |
| `msg.?field`   | `optional.of(msg.field)` if present, else `optional.none()`. |
| `map[?key]`    | The value at `key` if present, else `optional.none()`.       |
| `list[?index]` | The element at `index` if in bounds, else `optional.none()`. |

```php
Cel\evaluate('{"a": 1}.?a.value()',      []);           // 1
Cel\evaluate('{"a": 1}.?b.hasValue()',   []);           // false
Cel\evaluate('[10, 20][?5].hasValue()',  []);           // false
Cel\evaluate('m[?"key"].orValue("none")', ['m' => []]); // "none"
```

## Viral Propagation

Optional selection is _viral_: once a chain produces an optional, every subsequent selection and
index is treated as optional too, and an empty optional short-circuits the rest of the chain. These
are equivalent:

```
msg.?field.subfield
msg.?field.?subfield
```

```php
// short-circuits to optional.none() if `a` is missing, otherwise selects `b`
Cel\evaluate('{"a": {"b": 2}}.?a.b.value()', []); // 2
Cel\evaluate('{}.?a.b.hasValue()',           []); // false
```

## Optional Construction

Prefix a map key, message field, or list element with `?` to include it only when the right-hand
side is a _present_ optional. An empty optional is skipped. The right-hand side must be an
`optional(T)`.

```php
// map: the "nickname" entry is omitted because the optional is empty
Cel\evaluate('{"name": "Jane", ?"nickname": optional.none()}', []);
// => ["name" => "Jane"]

// list: only present optionals contribute elements
Cel\evaluate('[1, ?optional.of(2), ?optional.none(), 3]', []);
// => [1, 2, 3]

// message: set an optional field only when present (see Custom Types / Messages)
// Profile{name: "Jane", ?nickname: user.?nickname}
```

## Combining Optionals

| Method                  | Result                                                           |
| ----------------------- | ---------------------------------------------------------------- |
| `opt.or(other)`         | `opt` if it holds a value, otherwise `other` (another optional). |
| `opt.orValue(fallback)` | The contained value, otherwise `fallback` (a plain value).       |

`or` and `orValue` are **short-circuiting**: the alternative is only evaluated when `opt` is empty,
so `a.or(b).or(c)` stops at the first present optional.

```php
Cel\evaluate('optional.none().or(optional.of(9)).value()', []); // 9
Cel\evaluate('m[?"key"].orValue("default")', ['m' => []]);      // "default"
```

## Transforming Optionals

| Macro                     | Result                                                                                                                        |
| ------------------------- | ----------------------------------------------------------------------------------------------------------------------------- |
| `opt.optMap(v, expr)`     | If present, binds the value to `v`, evaluates `expr`, and wraps the result: `optional.of(expr)`. Otherwise `optional.none()`. |
| `opt.optFlatMap(v, expr)` | Like `optMap`, but `expr` must itself return an `optional(T)`, which is returned as-is (flattened).                           |

```php
Cel\evaluate('optional.of(42).optMap(n, n + 1).value()', []);        // 43
Cel\evaluate('{"k": {"n": "v"}}.?k.optFlatMap(m, m.?n).value()', []); // "v"
```

## List Helpers

| Function                | Result                                                                             |
| ----------------------- | ---------------------------------------------------------------------------------- |
| `list.first()`          | The first element as an optional, or `optional.none()` if the list is empty.       |
| `list.last()`           | The last element as an optional, or `optional.none()` if the list is empty.        |
| `optional.unwrap(list)` | A list of the values of all present optionals in `list` (empty optionals dropped). |
| `list.unwrapOpt()`      | Postfix form of `optional.unwrap`.                                                 |

```php
Cel\evaluate('[1, 2, 3].first().value()', []); // 1
Cel\evaluate('[1, 2, 3].last().value()',  []); // 3
Cel\evaluate('[].first().hasValue()',     []); // false

Cel\evaluate('optional.unwrap([optional.of(1), optional.none(), optional.of(3)])', []); // [1, 3]
Cel\evaluate('[optional.of(1), optional.none()].unwrapOpt()', []);                       // [1]
```

## Equality

Two optionals are equal when both are empty, or both hold equal values.

```php
Cel\evaluate('optional.none() == optional.none()'); // true
Cel\evaluate('optional.of(1) == optional.of(1)');   // true
Cel\evaluate('optional.of(1) == optional.none()');  // false
```

The runtime type of an optional is `optional_type`:

```php
Cel\evaluate('type(optional.of(1))'); // the `optional_type` type value
```

## PHP API

Optionals are represented at runtime by `Cel\Value\OptionalValue`:

```php
use Cel\Value\IntegerValue;
use Cel\Value\OptionalValue;

$present = OptionalValue::of(new IntegerValue(1));
$empty   = OptionalValue::none();

$present->hasValue();  // true
$present->value;       // IntegerValue(1)  (null when empty)
$empty->getRawValue(); // null
```
