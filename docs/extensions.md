# Standard Extensions Reference

CEL-PHP comes with a rich set of standard functions organized into extensions. This document provides a reference for all available functions and their signatures.

## Core Extension

The Core extension provides fundamental type conversion and utility functions.

### `bool`
Converts a value to a boolean.

**Signatures:**
- `(bool) -> bool`
- `(int) -> bool`
- `(uint) -> bool`
- `(float) -> bool`
- `(string) -> bool`
- `(bytes) -> bool`

### `bytes`
Converts a value to a bytes sequence.

**Signatures:**
- `(bytes) -> bytes`
- `(string) -> bytes`

### `float`
Converts a value to a float.

**Signatures:**
- `(float) -> float`
- `(int) -> float`
- `(uint) -> float`
- `(bool) -> float`
- `(string) -> float`
- `(bytes) -> float`

### `int`
Converts a value to an integer.

**Signatures:**
- `(int) -> int`
- `(uint) -> int`
- `(float) -> int`
- `(bool) -> int`
- `(string) -> int`
- `(bytes) -> int`

### `size`
Returns the size of a string, bytes, list, or map.

**Signatures:**
- `(string) -> int`
- `(bytes) -> int`
- `(list) -> int`
- `(map) -> int`

### `string`
Converts a value to a string.

**Signatures:**
- `(string) -> string`
- `(int) -> string`
- `(uint) -> string`
- `(float) -> string`
- `(bool) -> string`
- `(bytes) -> string`
- `(timestamp) -> string`
- `(duration) -> string`

### `typeOf`
Returns the type of a value as a string.

**Signatures:**
- `(any) -> string`

### `uint`
Converts a value to an unsigned integer.

**Signatures:**
- `(uint) -> uint`
- `(int) -> uint`
- `(float) -> uint`
- `(bool) -> uint`
- `(string) -> uint`
- `(bytes) -> uint`

---

## DateTime Extension

Provides functions for working with timestamps and durations.

### `duration`
Parses a duration string.

**Signatures:**
- `(string) -> duration`

### `getDayOfMonth`
Returns the day of the month.

**Signatures:**
- `(timestamp) -> int`
- `(timestamp, string) -> int`

### `getDayOfWeek`
Returns the day of the week.

**Signatures:**
- `(timestamp) -> int`
- `(timestamp, string) -> int`

### `getDayOfYear`
Returns the day of the year.

**Signatures:**
- `(timestamp) -> int`
- `(timestamp, string) -> int`

### `getFullYear`
Returns the full year.

**Signatures:**
- `(timestamp) -> int`
- `(timestamp, string) -> int`

### `getHours`
Returns the hours from a timestamp or duration.

**Signatures:**
- `(duration) -> int`
- `(timestamp) -> int`
- `(timestamp, string) -> int`

### `getMilliseconds`
Returns the milliseconds from a timestamp or duration.

**Signatures:**
- `(duration) -> int`
- `(timestamp) -> int`
- `(timestamp, string) -> int`

### `getMinutes`
Returns the minutes from a timestamp or duration.

**Signatures:**
- `(duration) -> int`
- `(timestamp) -> int`
- `(timestamp, string) -> int`

### `getMonth`
Returns the month.

**Signatures:**
- `(timestamp) -> int`
- `(timestamp, string) -> int`

### `getSeconds`
Returns the seconds from a timestamp or duration.

**Signatures:**
- `(duration) -> int`
- `(timestamp) -> int`
- `(timestamp, string) -> int`

### `now`
Returns the current timestamp.

**Signatures:**
- `() -> timestamp`

### `timestamp`
Parses a timestamp string.

**Signatures:**
- `(string) -> timestamp`

---

## List Extension

Provides functions for working with lists.

### `chunk`
Splits a list into chunks of a given size.

**Signatures:**
- `(list, int) -> list`

### `contains`
Checks if a list contains an element.

**Signatures:**
- `(list, any) -> bool`

### `flatten`
Flattens a list of lists into a single list.

**Signatures:**
- `(list) -> list`

### `join`
Joins a list of strings into a single string.

**Signatures:**
- `(list) -> string`
- `(list, string) -> string`

### `reverse`
Reverses the order of elements in a list.

**Signatures:**
- `(list) -> list`

### `sort`
Sorts the elements of a list.

**Signatures:**
- `(list) -> list`

---

## Math Extension

Provides mathematical functions.

### `baseConvert`
Converts a number string from one base to another.

**Signatures:**
- `(string, int, int) -> string`

### `clamp`
Clamps a number between a minimum and maximum value.

**Signatures:**
- `(int, int, int) -> int`
- `(float, float, float) -> float`

### `fromBase`
Converts a number string from a given base to an integer.

**Signatures:**
- `(string, int) -> int`

### `max`
Returns the maximum value from a list of numbers.

**Signatures:**
- `(list) -> any`

### `mean`
Calculates the mean of a list of numbers.

**Signatures:**
- `(list) -> float`

### `median`
Calculates the median of a list of numbers.

**Signatures:**
- `(list) -> float`

### `min`
Returns the minimum value from a list of numbers.

**Signatures:**
- `(list) -> any`

### `sum`
Calculates the sum of a list of integers.

**Signatures:**
- `(list) -> int`

### `toBase`
Converts an integer to a number string in a given base.

**Signatures:**
- `(int, int) -> string`

---

## String Extension

Provides functions for string manipulation.

### `contains`
Checks if a string contains a substring.

**Signatures:**
- `(string, string) -> bool`
- `(bytes, bytes) -> bool`

### `endsWith`
Checks if a string ends with a suffix.

**Signatures:**
- `(string, string) -> bool`
- `(bytes, bytes) -> bool`

### `indexOf`
Returns the first index of a substring.

**Signatures:**
- `(string, string) -> int`
- `(string, string, int) -> int`
- `(bytes, bytes) -> int`
- `(bytes, bytes, int) -> int`

### `lastIndexOf`
Returns the last index of a substring.

**Signatures:**
- `(string, string) -> int`
- `(string, string, int) -> int`
- `(bytes, bytes) -> int`
- `(bytes, bytes, int) -> int`

### `replace`
Replaces all occurrences of a substring.

**Signatures:**
- `(string, string, string) -> string`
- `(bytes, bytes, bytes) -> bytes`

### `split`
Splits a string by a delimiter.

**Signatures:**
- `(string, string) -> list`
- `(string, string, int) -> list`
- `(bytes, bytes) -> list`
- `(bytes, bytes, int) -> list`

### `startsWith`
Checks if a string starts with a prefix.

**Signatures:**
- `(string, string) -> bool`
- `(bytes, bytes) -> bool`

### `toAsciiLower`
Converts a string to ASCII lowercase.

**Signatures:**
- `(string) -> string`
- `(bytes) -> bytes`

### `toAsciiUpper`
Converts a string to ASCII uppercase.

**Signatures:**
- `(string) -> string`
- `(bytes) -> bytes`

### `toLower`
Converts a string to lowercase.

**Signatures:**
- `(string) -> string`
- `(bytes) -> bytes`

### `toUpper`
Converts a string to uppercase.

**Signatures:**
- `(string) -> string`
- `(bytes) -> bytes`

### `trim`
Trims whitespace from a string.

**Signatures:**
- `(string) -> string`
- `(string, string) -> string`
- `(bytes) -> bytes`
- `(bytes, bytes) -> bytes`

### `trimLeft`
Trims whitespace from the left of a string.

**Signatures:**
- `(string) -> string`
- `(string, string) -> string`
- `(bytes) -> bytes`
- `(bytes, bytes) -> bytes`

### `trimRight`
Trims whitespace from the right of a string.

**Signatures:**
- `(string) -> string`
- `(string, string) -> string`
- `(bytes) -> bytes`
- `(bytes, bytes) -> bytes`
