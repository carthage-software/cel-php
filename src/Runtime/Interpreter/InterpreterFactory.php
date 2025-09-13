<?php

declare(strict_types=1);

namespace Cel\Runtime\Interpreter;

use Cel\Runtime\Configuration;
use Cel\Runtime\Environment\EnvironmentInterface;
use Cel\Runtime\Function\FunctionRegistry;
use Cel\Runtime\Interpreter\TreeWalking\TreeWalkingInterpreter;

final readonly class InterpreterFactory
{
    public function __construct(
        private InterpreterPreference $preference = InterpreterPreference::TreeWalking,
    ) {}

    public function create(
        Configuration $configuration,
        FunctionRegistry $registry,
        EnvironmentInterface $environment,
    ): InterpreterInterface {
        return match ($this->preference) {
            InterpreterPreference::TreeWalking => new TreeWalkingInterpreter($configuration, $registry, $environment),
        };
    }
}
