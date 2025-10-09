<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension;

use Cel\Runtime\Function\FunctionInterface;
use Cel\Runtime\Operator\BinaryOperatorOverloadInterface;
use Cel\Runtime\Operator\UnaryOperatorOverloadInterface;

/**
 * Defines the contract for a CEL extension.
 */
interface ExtensionInterface
{
    /**
     * Retrieve all functions provided by this extension.
     *
     * @return list<FunctionInterface>
     */
    public function getFunctions(): array;

    /**
     * Retrieve all binary operator overloads provided by this extension.
     *
     * @return list<BinaryOperatorOverloadInterface>
     */
    public function getBinaryOperatorOverloads(): array;

    /**
     * Retrieve all unary operator overloads provided by this extension.
     *
     * @return list<UnaryOperatorOverloadInterface>
     */
    public function getUnaryOperatorOverloads(): array;
}
