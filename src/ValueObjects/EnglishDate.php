<?php

declare(strict_types=1);

namespace Nutandc\NepaliDateConverter\ValueObjects;

use DateTimeImmutable;

final class EnglishDate
{
    private const DAYS_EN = [
        1 => 'Sunday',
        2 => 'Monday',
        3 => 'Tuesday',
        4 => 'Wednesday',
        5 => 'Thursday',
        6 => 'Friday',
        7 => 'Saturday',
    ];

    public function __construct(
        public readonly int $year,
        public readonly int $month,
        public readonly int $day,
        public readonly int $dayOfWeek,
    ) {}

    public function toDateString(): string
    {
        return sprintf('%04d-%02d-%02d', $this->year, $this->month, $this->day);
    }

    public function toDateTimeImmutable(): DateTimeImmutable
    {
        return new DateTimeImmutable($this->toDateString());
    }

    /**
     * @return array{year:int, month:int, day:int, day_of_week:int}
     */
    public function toArray(): array
    {
        return [
            'year' => $this->year,
            'month' => $this->month,
            'day' => $this->day,
            'day_of_week' => $this->dayOfWeek,
        ];
    }

    public function dayOfWeekEnglishName(): string
    {
        return self::DAYS_EN[$this->dayOfWeek] ?? '';
    }

    public function toFormattedEnglish(): string
    {
        return $this->toDateTimeImmutable()->format('F j, Y');
    }
}
