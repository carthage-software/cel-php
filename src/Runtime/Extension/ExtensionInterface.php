<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension;

use Cel\Runtime\Function\FunctionInterface;

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
}
