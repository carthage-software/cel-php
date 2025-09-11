<?php

declare(strict_types=1);

namespace Cel\Syntax;

abstract readonly class Expression extends Node
{
    abstract public function getKind(): ExpressionKind;
}
