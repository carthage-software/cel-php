<?php

declare(strict_types=1);

namespace Cel\Extension\DateTime;

use Cel\Extension\ExtensionInterface;
use Override;

final readonly class DateTimeExtension implements ExtensionInterface
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new Function\NowFunction(),
            new Function\TimestampFunction(),
            new Function\DurationFunction(),
            new Function\GetSecondsFunction(),
            new Function\GetMinutesFunction(),
            new Function\GetHoursFunction(),
            new Function\GetMillisecondsFunction(),
            new Function\GetFullYearFunction(),
            new Function\GetMonthFunction(),
            new Function\GetDayOfYearFunction(),
            new Function\GetDayOfMonthFunction(),
            new Function\GetDayOfWeekFunction(),
        ];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getBinaryOperatorOverloads(): array
    {
        return [];
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
