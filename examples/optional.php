<?php

declare(strict_types=1);

namespace Cel\Examples;

use Cel;
use Psl\IO;

use function var_export;

require_once __DIR__ . '/../vendor/autoload.php';

// Look up a user's display name, falling back through optional selections:
// the explicit `nickname`, otherwise the `name`, otherwise a default.
const EXPRESSION = <<<CEL
        user.?nickname
            .or(user.?name)
            .orValue("anonymous")
    CEL;

try {
    $withNickname = Cel\evaluate(namespace\EXPRESSION, [
        'user' => ['name' => 'Jane Doe', 'nickname' => 'jd'],
    ]);
    IO\write_line('With nickname: %s', var_export($withNickname->getRawValue(), true)); // "jd"

    $withoutNickname = Cel\evaluate(namespace\EXPRESSION, [
        'user' => ['name' => 'Jane Doe'],
    ]);
    IO\write_line('Without nickname: %s', var_export($withoutNickname->getRawValue(), true)); // "Jane Doe"

    $anonymous = Cel\evaluate(namespace\EXPRESSION, [
        'user' => ['id' => 7],
    ]);
    IO\write_line('Anonymous: %s', var_export($anonymous->getRawValue(), true)); // "anonymous"

    // Build a map that only contains the fields that are actually present.
    $profile = Cel\evaluate('{"name": user.name, ?"nickname": user.?nickname}', [
        'user' => ['name' => 'Jane Doe'],
    ]);
    IO\write_line('Profile: %s', var_export($profile->getRawValue(), true)); // ["name" => "Jane Doe"]
} catch (Cel\Parser\Exception\ExceptionInterface $exception) {
    IO\write_error_line('Failed to parse expression: %s', $exception->getMessage());

    exit(1);
} catch (Cel\Exception\EvaluationException $e) {
    IO\write_error_line('Failed to evaluate expression: %s', $e->getMessage());

    exit(1);
}
