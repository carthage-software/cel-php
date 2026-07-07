# Upgrading to 2.0

This guide covers every behavioural and packaging change between **1.2.1** and **2.0**.

2.0 is a major release focused on **CEL specification / cel-go parity**. The bulk of the work
brings the evaluator's runtime semantics in line with the reference implementation, so most breaking
changes are corrections: expressions that previously produced a wrong result (or silently the wrong
type) now behave the way the CEL spec says they should.

There are two kinds of reader, and the guide is split accordingly:

- **Integrators**: PHP developers who install the package and call its API
  (`Cel\evaluate()`, `CommonExpressionLanguage`, `Runtime`, custom functions/operators). Jump to
  [Requirements](#1-requirements--installation) and [PHP API](#2-php-api--exceptions).
- **Expression authors**: anyone whose CEL expressions are evaluated by the library. Jump to
  [Expression behaviour](#3-expression-behaviour-changes). A change here can alter the result of an
  expression you already ship, so read this section even if you never touch the PHP API.

> [!IMPORTANT]
> The runtime has no separate type-check phase and none of the new semantics are opt-in. Every
> change under [Expression behaviour](#3-expression-behaviour-changes) is **always on** the moment
> you upgrade. If you evaluate untrusted or stored expressions, re-test them against 2.0.

---

## At a glance

| Area | Change | Who is affected |
| --- | --- | --- |
| Requirements | Required-extension set (`ext-bcmath`, `ext-intl`, `ext-mbstring`) is **unchanged**; only how they are declared changed | No action |
| Requirements | `php-standard-library` metapackage split into sub-packages | Handled by Composer |
| PHP API | Internal classes marked `@internal`; only `@api` symbols are supported | Integrators reaching past the entry points |
| PHP API | Thrown exceptions are now always project types implementing `Cel\Exception\ExceptionInterface` | Integrators with `catch` blocks |
| Conversions | `float()` renamed to `double()`; `typeOf()` removed in favour of `type()`; `dyn()` added | Expression authors |
| Numbers | int64 arithmetic and numeric conversions now **error on overflow** instead of wrapping | Expression authors |
| Numbers | `==`/`!=`/ordering are total and cross-type numeric (CEL proposal 210) | Expression authors |
| Literals | `0x`/`0o`/`0b` integer literals now parse correctly (were `0`) | Expression authors |
| Time | Timestamp/duration accessor semantics corrected; `string(timestamp)` reformatted | Expression authors |
| Maps | Map keys no longer collide across types; `bool` and large `uint` keys supported | Expression authors |

New, additive capabilities (optional values, `matches()`, two-variable comprehensions, timestamp /
duration arithmetic, protobuf well-known types, qualified-name resolution) are listed under
[New capabilities](#4-new-capabilities); they require no action.

---

## 1. Requirements & installation

PHP support is unchanged: **`~8.4 || ~8.5`**.

### Update the constraint

```bash
composer require carthage-software/cel-php:^2.0
```

### Extensions

**The set of PHP extensions you need is unchanged: `ext-bcmath`, `ext-intl`, and `ext-mbstring`.**
Only the way they are declared changed, and Composer resolves it for you. There is nothing to do
here in practice; the details are below for completeness.

- **`ext-bcmath`** remains a direct requirement (unchanged).

- **`ext-mbstring`** is now a *direct* requirement of cel-php (the source uses `mb_*` functions
  directly). On 1.x it was already installed transitively through `php-standard-library`, so any
  existing install already has it. Nothing to enable that was not already enabled.

- **`ext-intl`** is no longer listed in cel-php's own `require`, but it is **still required
  transitively** by `php-standard-library/date-time`, which cel-php depends on. So `ext-intl` is
  still part of the install and you should **not** remove it.

- **`ext-decimal`** is optional, exactly as on 1.x. It has never been a runtime dependency; you only
  need it if you register the optional decimal extension, `Cel\Extension\Decimal\DecimalExtension`.
  The only 2.0 change is a Composer `suggest` entry documenting it. If you use that extension, require
  the extension in your own project:

  ```json
  "require": { "ext-decimal": "*" }
  ```

### Dependency layout

The `php-standard-library/php-standard-library` metapackage was replaced by the specific sub-packages
cel-php actually uses (`php-standard-library/date-time`, `php-standard-library/default`). Composer
resolves this for you; there is nothing to change unless you pinned the metapackage yourself.

---

## 2. PHP API & exceptions

### `@api` vs `@internal`

Every class in the package is now annotated as either `@api` (supported, covered by semantic
versioning) or `@internal` (implementation detail, may change in any release). **Only depend on
`@api` symbols.**

Supported (`@api`) surfaces include the entry points and the types you compose them from:
`Cel\evaluate()`, `Cel\CommonExpressionLanguage`, `Cel\Runtime\Runtime`, `Cel\Environment\Environment`,
`Cel\Interpreter\Interpreter`, the `Cel\Value\*` value classes (`IntegerValue`, `StringValue`,
`TypeValue`, `OptionalValue`, `ValueKind`, and so on, but not the internal `WellKnownType` helper), the
`Cel\Syntax\*` / `Cel\Token\*` / `Cel\Span\*` nodes, every extension entry class (e.g.
`Cel\Extension\Core\CoreExtension`), the extension / function / operator *interfaces* (including the
overload-handler interfaces and `InterpreterInterface`), and every exception.

Now `@internal` (do not reference directly): the comprehension **macros** and macro infrastructure
under `Cel\Interpreter\Macro\*` (including `MacroInterface` and `MacroContextInterface`), and the
per-type operator/function **handler** classes (`...\Handler\...`). If you extended the library by
referencing a concrete handler, switch to the public interfaces described in
`docs/custom-functions.md`, `docs/custom-operators.md`, and `docs/extensions.md`.

### Exceptions

- **Everything the library throws implements `Cel\Exception\ExceptionInterface`.** 2.0 removed the
  remaining spots that threw native PHP types (`\LogicException`, `\InvalidArgumentException`,
  `\OverflowException`, `\OutOfRangeException`, and even a raw `\Error`). If you want a single
  catch-all, catch the marker interface:

  ```php
  try {
      $result = Cel\evaluate($expression, $variables);
  } catch (Cel\Exception\ExceptionInterface $e) {
      // parse or evaluation failure originating from cel-php
  }
  ```

- **If you catch specific native types, update them.** For example, code that caught
  `\InvalidArgumentException` or `\OverflowException` around base conversion / string offset helpers
  will no longer match; the library now raises `Cel\Exception\NumberFormatException` and
  `Cel\Exception\OutOfRangeException` (both implement `ExceptionInterface`).

- **New overflow errors surface as exceptions.** Integer arithmetic that overflowed int64 used to
  promote to a PHP float and then throw an uncaught `\TypeError`; it now raises
  `Cel\Exception\OverflowException`. See [Numbers and overflow](#numbers-and-overflow).

The public entry point signature is unchanged since 1.2.1:
`Cel\evaluate(InputInterface|string $expression, array $variables = [], Configuration $configuration = new Configuration())`.

---

## 3. Expression behaviour changes

These change the **result of evaluating an expression**. Each entry lists what changed, who is
affected, and what to do.

### Type conversions and built-in functions

**`float()` was renamed to `double()`.**
CEL's floating-point type is `double`, and the conversion function now matches.

```
float(1)     // 2.0: error, no such function
double(1)    // 1.0
```
- *Affected:* any expression calling `float(...)`.
- *Action:* replace `float(x)` with `double(x)`.

**`typeOf()` was removed; use `type()`.**
Type introspection now returns a first-class **type value** (e.g. `type(1)` is `int`, and
`type(1) == int` is `true`), matching the spec. The old `typeOf()` spelling no longer exists.
- *Affected:* expressions calling `typeOf(...)`.
- *Action:* replace `typeOf(x)` with `type(x)`.

**`dyn()` was added.** The identity function `dyn(x)` returns its argument (a no-op at runtime that
marks a value as dynamically typed). No action required.

**`bool(string)` is stricter.** It accepts only the exact CEL spellings:
`"1"`, `"t"`, `"true"`, `"TRUE"`, `"True"` for true and `"0"`, `"f"`, `"false"`, `"FALSE"`, `"False"`
for false; every other string raises a conversion error instead of being coerced.
- *Affected:* expressions relying on lenient string-to-bool coercion.
- *Action:* pass a canonical spelling, or convert upstream.

**`type(timestamp(...))` / `type(duration(...))` now report the protobuf type name.**
They return `google.protobuf.Timestamp` and `google.protobuf.Duration` (previously a short name).
This also affects `TimestampValue::getType()` / `DurationValue::getType()` at the PHP level.
- *Affected:* expressions or code that compare the string form of a timestamp/duration type.

### Numbers and overflow

**int64 arithmetic now errors on overflow.**
`+`, `-`, `*`, and unary negation on integers used to overflow into a PHP float (and then throw an
uncaught `\TypeError`). They now perform checked 64-bit arithmetic and raise
`Cel\Exception\OverflowException` when the result leaves the int64 range, matching CEL.
- *Affected:* expressions that can produce very large integer results.
- *Action:* guard inputs, or catch the overflow.

**Numeric conversion and range overflow are detected.**
- `int(double)` errors at the int64 float boundary and on `NaN`/`Infinity`.
- Unsigned `+` / `*` error when the result exceeds the 64-bit unsigned range.
- `timestamp(int|double|string)` and `duration(string)` reject values outside the CEL-valid range.

**Double division by zero no longer errors.**
`double` division by zero now yields `+Inf`, `-Inf`, or `NaN` (per IEEE-754), matching cel-go. Only
`int`/`uint` division by zero raises an error.

**`uint` literals above the signed 64-bit range are preserved.**
A literal such as `18446744073709551615u` used to be cast straight to a native `int` and clamped, so
`double(18446744073709551615u)` lost its magnitude. Such literals are now kept losslessly.

**Comparisons and equality are total and cross-type (CEL proposal 210).**
This is the largest single semantic change and it is **always on**. See
[`docs/equality-and-comparison.md`](docs/equality-and-comparison.md) for the full rules. Summary:

- `==` / `!=` **never raise on a type mismatch**: mismatched types are simply unequal
  (`1 == "1"` is `false`, not an error).
- `int`, `uint`, and `double` compare on a **single number line**, including nested in lists/maps
  (`1 == 1.0` → `true`, `1 < 1.5` → `true`, `-1 < 1u` → `true`), and heterogeneous numeric keys match
  in `in` and map indexing (`1.0 in {1: "a"}` → `true`).
- `null` is equal only to `null` and may be compared to anything without error.
- `NaN` is equal to nothing (including itself) and **cannot be ordered**: `x < 1.0` with `x = NaN`
  raises `Cel\Exception\UnsupportedOperationException`.

- *Affected:* any expression that compared values of different types and relied on the old
  error-on-mismatch behaviour, or that compared numbers across `int`/`uint`/`double`.
- *Action:* re-check comparison-heavy expressions; a mismatch that used to throw now returns
  `false`/`true`.

### Integer literals

**`0x`, `0o`, and `0b` literals now parse in their real base.**
Base-prefixed literals were cast with `(int) "0x…"`, which yields `0`, so every hex/octal/binary
literal evaluated to zero. They now convert via their actual base (decimal literals still round-trip
`INT64_MIN` correctly).

```
0xFF     // 2.0: 255       (was 0)
0o17     // 2.0: 15        (was 0)
0b1010   // 2.0: 10        (was 0)
```
- *Affected:* expressions using non-decimal integer literals; results change from `0` to the real
  value.

### Timestamps and durations

The timestamp/duration accessors were corrected to CEL semantics. **If you relied on the old
(incorrect) numbers, results will change.**

- `getDayOfMonth()` and `getDayOfYear()` are **zero-based** (`getDayOfYear` counting was also fixed).
- **`getDate()` was added** for the one-based day of month.
- Duration `getMilliseconds()` returns the **sub-second millisecond component**, not the total
  milliseconds.
- Timezone arguments now resolve through the **full IANA database** (`Cel\Util\TimezoneUtil`),
  accepting legacy aliases (`US/Central`), unsigned offsets (`02:00`) and `-00:00`.

**`string(timestamp)` formatting changed.** It now builds an RFC 3339 string from date-time
components and emits only the significant fractional digits (none when the sub-second part is zero),
instead of always emitting three rounded digits. This fixes cases such as
`9999-12-31T23:59:59.999999999Z`, which previously rounded up into an out-of-range value.

**RFC 3339 parsing was rewritten** onto the proleptic Gregorian calendar (year `0001` was two days
off), and now preserves sub-microsecond nanoseconds, validates the date, and handles `±HH:MM`
offsets.

- *Affected:* expressions using timestamp/duration accessors, `string(timestamp)`, or timestamp
  string parsing.
- *Action:* if you compared against the old accessor values or formatted output, update your
  expectations to the corrected values.

### Maps and indexing

**Map keys no longer collide across types.**
Keys are stored internally so that distinct CEL key types stay distinct:

```
size({"1": "a", 1: "b"})   // 1.0: 1, 2.0: 2   (string "1" and int 1 are different keys)
```
`bool` keys are now supported (`{true: "a"}`), and unsigned keys beyond `PHP_INT_MAX` are addressable
losslessly.
- *Affected:* expressions with maps that mixed string and numeric keys, or used `bool`/large-`uint`
  keys.

**List indexing accepts integral numeric indices.**
`list[i]` accepts an `int`, a `uint`, or an **integral** `double` (`0.0`); a non-integral double
(`0.1`) errors. List positions never accept map-style keys.

### Comprehension error handling

**`all()` / `exists()` now absorb predicate errors the way CEL requires.**
The first predicate error is held pending: if a *later* element short-circuits the macro (a `false`
for `all`, a `true` for `exists`), that result is returned and the pending error is discarded. If no
element determines the result, the pending error is re-thrown at the end. `existsOne` does **not**
absorb errors; it has no such short-circuit, so a predicate error propagates immediately.
- *Affected:* expressions whose predicates can error on some elements; a case that used to throw may
  now return `false`/`true` when a later element decides the result.

---

## 4. New capabilities

These are additive; they don't change existing expressions and need no migration, but they are the
reason to move to 2.0.

- **Optional values (CEL proposal 246).** `optional.of`, `optional.none`, `optional.ofNonZeroValue`,
  `.orValue`, optional field/index selection, and `has()` seeing through optionals. See
  [`docs/optional-values.md`](docs/optional-values.md).
- **`matches()` regex function.** `matches(s, re)` and `s.matches(re)` test a Unicode, unanchored
  (partial) match; an invalid pattern errors.
- **Two-variable comprehensions.** `all`/`exists`/`existsOne(i, v, p)` plus `transformList` and
  `transformMap`.
- **Timestamp and duration arithmetic.** `timestamp ± duration`, `duration ± duration`,
  `duration + timestamp`, and `timestamp - timestamp` (yielding a duration), all range-checked.
- **`google.protobuf` well-known types.** The nine scalar wrappers (e.g. `Int32Value`, `BoolValue`,
  `StringValue`) construct and unwrap to their primitive, and `google.protobuf.Value` constructs to
  `null`; unknown fields are rejected. Note that `google.protobuf.Timestamp` and
  `google.protobuf.Duration` are **not** constructible as message literals; timestamps and durations
  come from the `timestamp()` / `duration()` functions.
- **Qualified-name resolution and the leading-dot operator.** CEL longest-prefix name resolution and
  absolute references (`.foo`).

---

## 5. Migration checklist

1. **Bump the constraint:** `composer require carthage-software/cel-php:^2.0`.
2. **Extensions: nothing to change.** You still need `ext-bcmath`, `ext-intl`, and `ext-mbstring`
   (the same set as 1.x); do **not** remove `ext-intl` (it is still required transitively). If you
   use the Decimal extension, keep `ext-decimal` in your own project (unchanged from 1.x).
3. **Search your PHP for `catch`** of native exception types thrown by cel-php and switch to
   `Cel\Exception\ExceptionInterface` (or the specific project exceptions).
4. **Stop referencing `@internal` classes** (the macros and concrete handlers); move to the public
   interfaces.
5. **Audit stored / user-supplied expressions** for:
   - `float(` → `double(`, `typeOf(` → `type(`;
   - hex/octal/binary literals (they no longer evaluate to `0`);
   - integer arithmetic and numeric conversions that could now overflow-error;
   - cross-type `==`/`!=`/ordering that relied on the old error-on-mismatch behaviour;
   - timestamp/duration accessor values and `string(timestamp)` output;
   - maps mixing string and numeric keys.
6. **Run your expression test-suite** against 2.0 before deploying.

If something here is unclear or you hit a case the guide doesn't cover, please open an issue:
<https://github.com/carthage-software/cel-php/issues>.
