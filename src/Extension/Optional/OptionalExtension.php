<?php

declare(strict_types=1);

namespace Cel\Extension\Optional;

use Cel\Extension\ExtensionInterface;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Override;

/**
 * Provides the optional value type functions and operators defined by
 * CEL proposal 246 (Optional Values):
 *
 * - `optional.of`, `optional.ofNonZeroValue`, `optional.none`
 * - `optional(T).value()`, `optional(T).hasValue()`
 * - `list(T).first()`, `list(T).last()`
 * - `optional.unwrap(list(optional(T)))`, `list(optional(T)).unwrapOpt()`
 * - equality between optional values
 *
 * The optional selection/indexing syntax (`.?field`, `[?key]`), optional
 * construction (`{?key: v}`, `Msg{?field: v}`, `[?v]`), and the `or`, `orValue`,
 * `optMap`, and `optFlatMap` macros are handled by the interpreter and macro
 * registry rather than this extension.
 */
final readonly class OptionalExtension implements ExtensionInterface
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new Function\OfFunction(),
            new Function\OfNonZeroValueFunction(),
            new Function\NoneFunction(),
            new Function\ValueFunction(),
            new Function\HasValueFunction(),
            new Function\FirstFunction(),
            new Function\LastFunction(),
            new Function\UnwrapFunction(),
            new Function\UnwrapOptFunction(),
        ];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getBinaryOperatorOverloads(): array
    {
        return [
            new BinaryOperator\EqualityOperator(BinaryOperatorKind::Equal),
            new BinaryOperator\EqualityOperator(BinaryOperatorKind::NotEqual),
        ];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getUnaryOperatorOverloads(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getMessageTypes(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getValueResolvers(): array
    {
        return [];
    }
}
