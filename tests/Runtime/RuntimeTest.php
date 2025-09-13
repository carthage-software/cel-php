<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime;

use Cel\Runtime\Exception\NoSuchOverloadException;
use Cel\Runtime\Exception\OverflowException;
use Cel\Runtime\Exception\RuntimeException;
use Cel\Runtime\Interpreter\TreeWalking\TreeWalkingInterpreter;
use Cel\Runtime\Runtime;
use Cel\Runtime\Value\BooleanValue;
use Cel\Runtime\Value\UnsignedIntegerValue;
use Cel\Runtime\Value\Value;
use Cel\Span\Span;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;

#[CoversClass(Runtime::class)]
#[CoversClass(TreeWalkingInterpreter::class)]
#[Medium]
final class RuntimeTest extends RuntimeTestCase
{
    /**
     * @return iterable<string, array{0: string, 1: array<string, mixed>, 2: Value|RuntimeException}>
     */
    #[Override]
    public static function provideEvaluationCases(): iterable
    {
        yield 'Transaction validation: success' =>
            [
                'request.amount < account.balance + account.overdraft_limit',
                [
                    'request' => ['amount' => 50.0],
                    'account' => ['balance' => 100.0, 'overdraft_limit' => 20.0],
                ],
                new BooleanValue(true),
            ];

        yield 'Transaction validation: failure' =>
            [
                'request.amount < account.balance',
                [
                    'request' => ['amount' => 150.0],
                    'account' => ['balance' => 100.0],
                ],
                new BooleanValue(false),
            ];

        yield 'RBAC: admin access' =>
            [
                '\'admin\' in user.roles',
                [
                    'user' => ['roles' => ['editor', 'admin']],
                ],
                new BooleanValue(true),
            ];

        yield 'RBAC: owner access' =>
            [
                'user.id == resource.owner_id',
                [
                    'user' => ['id' => 'user-123'],
                    'resource' => ['owner_id' => 'user-123'],
                ],
                new BooleanValue(true),
            ];

        yield 'Error: Type mismatch in addition' =>
            [
                'user.login_attempts + \'1\'',
                [
                    'user' => ['login_attempts' => 5],
                ],
                new NoSuchOverloadException('Cannot add `int` and `string`', new Span(0, 25)),
            ];

        yield 'Error: Unsigned integer overflow' =>
            [
                'inventory.stock_count - request.quantity',
                [
                    'inventory' => ['stock_count' => new UnsignedIntegerValue(10)],
                    'request' => ['quantity' => new UnsignedIntegerValue(50)],
                ],
                new OverflowException('Unsigned integer overflow on subtraction', new Span(0, 39)),
            ];
    }
}
