<?php

declare(strict_types=1);

namespace Cel\Extension;

use Cel\Function\FunctionInterface;
use Cel\Message\MessageInterface;
use Cel\Operator\BinaryOperatorOverloadInterface;
use Cel\Operator\UnaryOperatorOverloadInterface;
use Cel\Value\Resolver\ValueResolverInterface;

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

    /**
     * Retrieve all message types provided by this extension.
     *
     * Extensions can register message types that will be automatically
     * available without requiring users to explicitly enable them.
     *
     * @return array<class-string<MessageInterface>, list<string>> Mapping of message classes to their aliases
     */
    public function getMessageTypes(): array;

    /**
     * Retrieve all value resolvers provided by this extension.
     *
     * Value resolvers enable extensions to provide custom type conversion
     * logic for raw PHP values (e.g., converting BCMath\Number to BCNumber).
     *
     * @return list<ValueResolverInterface>
     */
    public function getValueResolvers(): array;
}
