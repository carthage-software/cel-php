<?php

declare(strict_types=1);

use Psl\IO;

require_once __DIR__ . '/../vendor/autoload.php';

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
    IO\write_error_line('Parse error: %s', $exception->getMessage());

    exit(1);
} catch (Cel\Runtime\Exception\IncompatibleValueTypeException $e) {
    IO\write_error_line('A value could not be converted: %s', $e->getMessage());

    exit(1);
} catch (Cel\Runtime\Exception\RuntimeException $e) {
    IO\write_error_line('Evaluation error: %s', $e->getMessage());

    exit(1);
}
