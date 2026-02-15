<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime;

use Cel\Exception\EvaluationException;
use Cel\Exception\MessageConstructionException;
use Cel\Exception\NoSuchOverloadException;
use Cel\Exception\NoSuchTypeException;
use Cel\Exception\OverflowException;
use Cel\Runtime\Configuration;
use Cel\Span\Span;
use Cel\Tests\Fixture\CommentMessage;
use Cel\Tests\Fixture\UserMessage;
use Cel\Value\BooleanValue;
use Cel\Value\MessageValue;
use Cel\Value\StringValue;
use Cel\Value\UnsignedIntegerValue;
use Cel\Value\Value;
use Override;

class RuntimeTest extends RuntimeTestCase
{
    /**
     * @return iterable<string, array{
     *     0: string,
     *     1: array<string, mixed>,
     *     2: Value|EvaluationException,
     *     3?: null|Configuration
     * }>
     */
    #[Override]
    public static function provideEvaluationCases(): iterable
    {
        yield 'Transaction validation: success' => [
            'request.amount < account.balance + account.overdraft_limit',
            [
                'request' => ['amount' => 50.0],
                'account' => ['balance' => 100.0, 'overdraft_limit' => 20.0],
            ],
            new BooleanValue(true),
        ];

        yield 'Transaction validation: failure' => [
            'request.amount < account.balance',
            [
                'request' => ['amount' => 150.0],
                'account' => ['balance' => 100.0],
            ],
            new BooleanValue(false),
        ];

        yield 'RBAC: admin access' => [
            '\'admin\' in user.roles',
            [
                'user' => ['roles' => ['editor', 'admin']],
            ],
            new BooleanValue(true),
        ];

        yield 'Form: field in answers' => [
            '\'field\' in answers',
            [
                'answers' => [
                    'field' => 'available',
                ],
            ],
            new BooleanValue(true),
        ];
        yield 'Form: field not in answers' => [
            '\'field\' in answers',
            [
                'answers' => [
                    'foo' => 'bar',
                ],
            ],
            new BooleanValue(false),
        ];

        yield 'RBAC: owner access' => [
            'user.id == resource.owner_id',
            [
                'user' => ['id' => 'user-123'],
                'resource' => ['owner_id' => 'user-123'],
            ],
            new BooleanValue(true),
        ];

        yield 'Error: Type mismatch in addition' => [
            'user.login_attempts + \'1\'',
            [
                'user' => ['login_attempts' => 5],
            ],
            new NoSuchOverloadException('No such overload for `int` + `string`', new Span(0, 25)),
        ];

        yield 'Error: Invalid unary negation on string' => [
            '-user.name',
            [
                'user' => ['name' => 'Alice'],
            ],
            new NoSuchOverloadException('No such overload for -`string`', new Span(0, 10)),
        ];

        yield 'Error: Invalid unary negation on boolean' => [
            '-user.active',
            [
                'user' => ['active' => true],
            ],
            new NoSuchOverloadException('No such overload for -`bool`', new Span(0, 12)),
        ];

        yield 'Error: Invalid logical not on integer' => [
            '!user.age',
            [
                'user' => ['age' => 25],
            ],
            new NoSuchOverloadException('No such overload for !`int`', new Span(0, 9)),
        ];

        yield 'Error: Invalid logical not on string' => [
            '!user.name',
            [
                'user' => ['name' => 'Bob'],
            ],
            new NoSuchOverloadException('No such overload for !`string`', new Span(0, 10)),
        ];

        yield 'Error: Unsigned integer overflow' => [
            'inventory.stock_count - request.quantity',
            [
                'inventory' => ['stock_count' => new UnsignedIntegerValue(10)],
                'request' => ['quantity' => new UnsignedIntegerValue(50)],
            ],
            new OverflowException('Unsigned integer overflow on subtraction', new Span(0, 39)),
        ];

        yield 'Message' => [
            'cel.tests.fixture.UserMessage { name: "azjezz", email: "azjezz@carthage.software" }',
            [],
            new MessageValue(new UserMessage('azjezz', 'azjezz@carthage.software'), [
                'name' => new StringValue('azjezz'),
                'email' => new StringValue('azjezz@carthage.software'),
            ]),
            Configuration::forAllowedMessages([UserMessage::class]),
        ];

        yield 'Message Invalid Fields' => [
            'cel.tests.fixture.UserMessage { name: 1, email: "azjezz@carthage.software" }',
            [],
            new MessageConstructionException('f', Span::zero()),
            Configuration::forAllowedMessages([UserMessage::class]),
        ];

        yield 'Message Missing Fields' => [
            'cel.tests.fixture.UserMessage { email: "azjezz@carthage.software" }',
            [],
            new MessageConstructionException('f', Span::zero()),
            Configuration::forAllowedMessages([UserMessage::class]),
        ];

        yield 'Message Extra Fields' => [
            'cel.tests.fixture.UserMessage { name: "azjezz", email: "azjezz@carthage.software", age: 30 }',
            [],
            new MessageConstructionException(
                'Failed to create message of type `cel.tests.fixture.UserMessage`: Invalid fields for `UserMessage`, expected `name` and `email` of type `string`',
                Span::zero(),
            ),
            Configuration::forAllowedMessages([UserMessage::class]),
        ];

        yield 'Disable Messages' => [
            'cel.tests.fixture.UserMessage { name: "azjezz", email: "azjezz@carthage.software" }',
            [],
            new NoSuchTypeException(
                'Message type `cel.tests.fixture.UserMessage` does not exist or is not allowed per configuration.',
                Span::zero(),
            ),
            Configuration::forAllowedMessages([]),
        ];

        yield 'Disable Message Type' => [
            'cel.tests.fixture.UserMessage { name: "azjezz", email: "azjezz@carthage.software" }',
            [],
            new NoSuchTypeException(
                'Message type `cel.tests.fixture.UserMessage` does not exist or is not allowed per configuration.',
                Span::zero(),
            ),
            Configuration::forAllowedMessages([CommentMessage::class]),
        ];

        yield 'Using Message Alias' => [
            'my_package.UserMessage { name: "azjezz", email: "azjezz@carthage.software" }',
            [],
            new MessageValue(new UserMessage('azjezz', 'azjezz@carthage.software'), [
                'name' => new StringValue('azjezz'),
                'email' => new StringValue('azjezz@carthage.software'),
            ]),
            Configuration::forAllowedMessages([UserMessage::class], ['my_package.UserMessage' => UserMessage::class]),
        ];

        yield 'Enforced Message Alias Usage' => [
            'my_package.UserMessage { name: "azjezz", email: "azjezz@carthage.software" }',
            [],
            new MessageValue(new UserMessage('azjezz', 'azjezz@carthage.software'), [
                'name' => new StringValue('azjezz'),
                'email' => new StringValue('azjezz@carthage.software'),
            ]),
            Configuration::forAllowedMessages(
                [UserMessage::class],
                ['my_package.UserMessage' => UserMessage::class],
                true,
            ),
        ];

        yield 'Using Message Without Alias' => [
            'cel.tests.fixture.UserMessage { name: "azjezz", email: "azjezz@carthage.software" }',
            [],
            new MessageValue(new UserMessage('azjezz', 'azjezz@carthage.software'), [
                'name' => new StringValue('azjezz'),
                'email' => new StringValue('azjezz@carthage.software'),
            ]),
            Configuration::forAllowedMessages([UserMessage::class], ['my_package.UserMessage' => UserMessage::class]),
        ];

        yield 'Forbid Message FQCN Without Alias' => [
            'cel.tests.fixture.UserMessage { name: "azjezz", email: "azjezz@carthage.software" }',
            [],
            new NoSuchTypeException(
                'Message type `cel.tests.fixture.UserMessage` does not exist or is not allowed per configuration.',
                Span::zero(),
            ),
            Configuration::forAllowedMessages(
                [UserMessage::class],
                ['my_package.UserMessage' => UserMessage::class],
                true,
            ),
        ];

        yield 'Division by zero: 10 / 0' => [
            '10 / 0',
            [],
            new EvaluationException('Failed to evaluate division: division by zero', new Span(0, 5)),
        ];

        yield 'Modulus by zero: 10 / 0' => [
            '10 % 0',
            [],
            new EvaluationException('Failed to evaluate modulo: division by zero', new Span(0, 5)),
        ];

        yield 'Division by zero: 10u / 0u' => [
            '10u / 0u',
            [],
            new EvaluationException('Failed to evaluate division: division by zero', new Span(0, 5)),
        ];

        yield 'Modulus by zero: 10u / 0u' => [
            '10u % 0u',
            [],
            new EvaluationException('Failed to evaluate modulo: division by zero', new Span(0, 5)),
        ];
    }
}
