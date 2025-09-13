<?php

declare(strict_types=1);

namespace Cel\Runtime;

use Cel\Runtime\Environment\EnvironmentInterface;
use Cel\Runtime\Extension\Core\CoreExtension;
use Cel\Runtime\Extension\DateTime\DateTimeExtension;
use Cel\Runtime\Extension\ExtensionInterface;
use Cel\Runtime\Extension\Lists\ListsExtension;
use Cel\Runtime\Extension\Math\MathExtension;
use Cel\Runtime\Extension\Strings\StringsExtension;
use Cel\Runtime\Function\FunctionRegistry;
use Cel\Runtime\Interpreter\InterpreterFactory;
use Cel\Syntax\Expression;
use Override;

final readonly class Runtime implements RuntimeInterface
{
    public function __construct(
        private InterpreterFactory $factory = new InterpreterFactory(),
        private FunctionRegistry $registry = new FunctionRegistry(),
    ) {
        $this->register(new CoreExtension());
        $this->register(new StringsExtension());
        $this->register(new ListsExtension());
        $this->register(new MathExtension());
        $this->register(new DateTimeExtension());
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function register(ExtensionInterface $extension): void
    {
        $this->registry->register($extension);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function run(Expression $expression, EnvironmentInterface $environment): RuntimeReceipt
    {
        $interpreter = $this->factory->create($this->registry, $environment);
        $interpreter->reset(); // Ensure the interpreter is in a clean state before running.

        $result = $interpreter->run($expression);
        $idempotent = $interpreter->wasIdempotent();

        $interpreter->reset(); // Reset the interpreter state after running, in case of reuse.

        return new RuntimeReceipt($result, $idempotent);
    }
}
