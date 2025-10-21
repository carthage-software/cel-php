# Standard Extensions Reference

CEL-PHP comes with several built-in extensions that provide functions and operators for common operations.

## Core Extension

The Core extension provides fundamental operators and type conversion functions.

### Operators

#### Arithmetic Operators

```php
1 + 2          // Addition: 3
5 - 3          // Subtraction: 2
4 * 3          // Multiplication: 12
10 / 2         // Division: 5
10 % 3         // Modulo: 1
```

#### Comparison Operators

```php
1 < 2          // Less than: true
1 <= 1         // Less than or equal: true
2 > 1          // Greater than: true
2 >= 2         // Greater than or equal: true
```

#### Equality Operators

```php
1 == 1         // Equal: true
1 != 2         // Not equal: true
```

#### Logical Operators

```php
true && false  // Logical AND: false
true || false  // Logical OR: true
!true          // Logical NOT: false
```

#### Membership Operator

```php
'a' in ['a', 'b', 'c']       // true
'key' in {'key': 'value'}    // true
```

### Type Conversion Functions

#### `int()` - Convert to integer

```php
int('42')      // 42
int(3.14)      // 3
int(b'42')     // 42
```

#### `uint()` - Convert to unsigned integer

```php
uint('42')     // 42u
uint(42)       // 42u
uint(b'42')    // 42u
```

#### `double()` / `float()` - Convert to floating point

```php
double('3.14') // 3.14
float(3)       // 3.0
```

#### `string()` - Convert to string

```php
string(42)     // '42'
string(true)   // 'true'
```

#### `bytes()` - Convert to bytes

```php
bytes('hello') // b'hello'
```

#### `bool()` - Convert to boolean

```php
bool('true')   // true
bool(0)        // false
bool(1)        // true
```

### Utility Functions

#### `type()` - Get the type of a value

```php
type(42)       // 'int'
type('hello')  // 'string'
type([1, 2])   // 'list'
```

#### `size()` - Get the size of a collection

```php
size('hello')  // 5
size([1, 2, 3])// 3
size({'a': 1}) // 1
```

## String Extension

The String extension provides functions for string manipulation.

### String Functions

#### `contains()` - Check if string contains substring

```php
'hello world'.contains('world')  // true
'hello'.contains('bye')          // false
```

#### `startsWith()` - Check if string starts with prefix

```php
'hello world'.startsWith('hello')  // true
'hello'.startsWith('world')        // false
```

#### `endsWith()` - Check if string ends with suffix

```php
'hello world'.endsWith('world')  // true
'hello'.endsWith('hello')        // true
```

#### `indexOf()` - Find index of substring

```php
'hello world'.indexOf('world')     // 6
'hello world'.indexOf('world', 7)  // -1 (not found after index 7)
```

#### `lastIndexOf()` - Find last index of substring

```php
'hello world hello'.lastIndexOf('hello')  // 12
```

#### `replace()` - Replace substring

```php
'hello world'.replace('world', 'CEL')  // 'hello CEL'
```

#### `split()` - Split string by separator

```php
'a,b,c'.split(',')     // ['a', 'b', 'c']
'hello'.split('')      // ['h', 'e', 'l', 'l', 'o']
```

#### `join()` - Join list elements

```php
['a', 'b', 'c'].join(',')  // 'a,b,c'
['a', 'b'].join()          // 'ab'
```

#### `toLowerCase()` / `toAsciiLower()` - Convert to lowercase

```php
'HELLO'.toLowerCase()      // 'hello'
'HELLO'.toAsciiLower()     // 'hello'
```

#### `toUpperCase()` / `toAsciiUpper()` - Convert to uppercase

```php
'hello'.toUpperCase()      // 'HELLO'
'hello'.toAsciiUpper()     // 'HELLO'
```

#### `trim()` / `trimLeft()` / `trimRight()` - Trim whitespace

```php
'  hello  '.trim()         // 'hello'
'  hello'.trimLeft()       // 'hello'
'hello  '.trimRight()      // 'hello'

// With custom characters
'***hello***'.trim('*')    // 'hello'
```

## List Extension

The List extension provides functions for list manipulation.

### List Functions

#### `chunk()` - Split list into chunks

```php
[1, 2, 3, 4, 5].chunk(2)   // [[1, 2], [3, 4], [5]]
```

#### `contains()` - Check if list contains value

```php
[1, 2, 3].contains(2)      // true
[1, 2, 3].contains(4)      // false
```

#### `flatten()` - Flatten nested lists

```php
[[1, 2], [3, 4]].flatten()  // [1, 2, 3, 4]
```

#### `reverse()` - Reverse list

```php
[1, 2, 3].reverse()        // [3, 2, 1]
```

#### `sort()` - Sort list

```php
[3, 1, 2].sort()           // [1, 2, 3]
```

## DateTime Extension

The DateTime extension provides functions for working with timestamps and durations.

### DateTime Functions

#### `timestamp()` - Create timestamp

```php
timestamp('2024-01-01T00:00:00Z')  // Timestamp value
timestamp(1704067200)               // From Unix timestamp
```

#### `duration()` - Create duration

```php
duration('1h30m')          // 1 hour 30 minutes
duration('5s')             // 5 seconds
duration('100ms')          // 100 milliseconds
```

#### Timestamp Accessors

```php
timestamp('2024-01-15T10:30:45Z').getFullYear()     // 2024
timestamp('2024-01-15T10:30:45Z').getMonth()        // 0 (0-indexed)
timestamp('2024-01-15T10:30:45Z').getDayOfMonth()   // 14 (0-indexed)
timestamp('2024-01-15T10:30:45Z').getDayOfWeek()    // 1 (Monday, 0-indexed)
timestamp('2024-01-15T10:30:45Z').getDayOfYear()    // 14 (0-indexed)
timestamp('2024-01-15T10:30:45Z').getHours()        // 10
timestamp('2024-01-15T10:30:45Z').getMinutes()      // 30
timestamp('2024-01-15T10:30:45Z').getSeconds()      // 45
timestamp('2024-01-15T10:30:45Z').getMilliseconds() // 0
```

#### `now()` - Get current timestamp

```php
now()  // Current timestamp
```

## Math Extension

The Math extension provides mathematical functions.

### Math Functions

#### `min()` / `max()` - Find minimum/maximum

```php
min([1, 2, 3, 4, 5])       // 1
max([1, 2, 3, 4, 5])       // 5
```

#### `sum()` - Sum of numbers

```php
sum([1, 2, 3, 4, 5])       // 15
```

#### `mean()` - Average of numbers

```php
mean([1, 2, 3, 4, 5])      // 3.0
```

#### `median()` - Median value

```php
median([1, 2, 3, 4, 5])    // 3
median([1, 2, 3, 4])       // 2.5
```

#### `clamp()` - Clamp value to range

```php
clamp(5, 0, 10)            // 5
clamp(15, 0, 10)           // 10
clamp(-5, 0, 10)           // 0
```

#### Base Conversion

```php
toBase(255, 16)            // 'ff'
toBase(255, 2)             // '11111111'
fromBase('ff', 16)         // 255
fromBase('11111111', 2)    // 255
baseConvert('ff', 16, 10)  // '255'
```

## Using Extensions

Extensions are enabled by default. You can control which extensions are available:

```php
use Cel;

// Disable standard extensions
$configuration = new Cel\Runtime\Configuration(
    enableStandardExtensions: false
);

$runtime = new Cel\Runtime\Runtime(configuration: $configuration);

// Register only specific extensions
$runtime->register(new Cel\Extension\Core\CoreExtension());
$runtime->register(new Cel\Extension\String\StringExtension());

$cel = new Cel\CommonExpressionLanguage(runtime: $runtime);
```

## Next Steps

- Learn how to create [Custom Functions](./custom-functions.md)
- Learn how to create [Custom Operators](./custom-operators.md)
- Learn about [Value Resolvers](./value-resolvers.md)
