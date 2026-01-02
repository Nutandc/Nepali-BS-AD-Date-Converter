<?php

declare(strict_types=1);

namespace Nutandc\NepaliDateConverter\ValueObjects;

use Nutandc\NepaliDateConverter\Enums\NepaliDateFormat;

final class NepaliDate
{
    private const MONTHS_EN = [
        1 => 'Baisakh',
        2 => 'Jestha',
        3 => 'Ashar',
        4 => 'Shrawan',
        5 => 'Bhadra',
        6 => 'Ashoj',
        7 => 'Kartik',
        8 => 'Manghir',
        9 => 'Poush',
        10 => 'Magh',
        11 => 'Falgun',
        12 => 'Chaitra',
    ];

    private const DAYS_EN = [
        1 => 'Sunday',
        2 => 'Monday',
        3 => 'Tuesday',
        4 => 'Wednesday',
        5 => 'Thursday',
        6 => 'Friday',
        7 => 'Saturday',
    ];

    private const MONTHS_NP = [
        1 => 'वैशाख',
        2 => 'जेठ',
        3 => 'असार',
        4 => 'साउन',
        5 => 'भदौ',
        6 => 'असोज',
        7 => 'कार्तिक',
        8 => 'मंसिर',
        9 => 'पुष',
        10 => 'माघ',
        11 => 'फागुन',
        12 => 'चैत',
    ];

    private const DAYS_NP = [
        1 => 'आइतवार',
        2 => 'सोमवार',
        3 => 'मङ्गलवार',
        4 => 'बुधवार',
        5 => 'बिहिवार',
        6 => 'शुक्रवार',
        7 => 'शनिवार',
    ];

    private const DIGITS_NP = [
        0 => '०',
        1 => '१',
        2 => '२',
        3 => '३',
        4 => '४',
        5 => '५',
        6 => '६',
        7 => '७',
        8 => '८',
        9 => '९',
    ];

    public function __construct(
        public readonly int $year,
        public readonly int $month,
        public readonly int $day,
        public readonly int $dayOfWeek,
    ) {
    }

    public function toDateString(): string
    {
        return sprintf('%04d-%02d-%02d', $this->year, $this->month, $this->day);
    }

    /**
     * @return array{year:int, month:int, day:int, day_of_week:int}|array{year:string, month:string, day:string, day_of_week:string}|string
     */
    public function format(NepaliDateFormat $format): array|string
    {
        return match ($format) {
            NepaliDateFormat::DateString => $this->toDateString(),
            NepaliDateFormat::FormattedEnglish => $this->toFormattedEnglish(),
            NepaliDateFormat::FormattedNepali => $this->toFormattedNepali(),
            NepaliDateFormat::Array => $this->toArray(),
            NepaliDateFormat::FormattedArray => $this->toFormattedArray(),
        };
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

    /**
     * @return array{year:string, month:string, day:string, day_of_week:string}
     */
    public function toFormattedArray(): array
    {
        return [
            'year' => $this->formatNepaliNumber($this->year),
            'month' => $this->monthNepaliName(),
            'day' => $this->formatNepaliNumber($this->day),
            'day_of_week' => $this->dayOfWeekNepaliName(),
        ];
    }

    public function toFormattedEnglish(): string
    {
        return sprintf(
            '%d %s %d, %s',
            $this->day,
            $this->monthEnglishName(),
            $this->year,
            $this->dayOfWeekEnglishName(),
        );
    }

    public function toFormattedNepali(): string
    {
        return sprintf(
            '%s %s %s, %s',
            $this->formatNepaliNumber($this->year),
            $this->monthNepaliName(),
            $this->formatNepaliNumber($this->day),
            $this->dayOfWeekNepaliName(),
        );
    }

    public function monthEnglishName(): string
    {
        return self::MONTHS_EN[$this->month] ?? '';
    }

    public function monthNepaliName(): string
    {
        return self::MONTHS_NP[$this->month] ?? '';
    }

    public function dayOfWeekEnglishName(): string
    {
        return self::DAYS_EN[$this->dayOfWeek] ?? '';
    }

    public function dayOfWeekNepaliName(): string
    {
        return self::DAYS_NP[$this->dayOfWeek] ?? '';
    }

    private function formatNepaliNumber(int $value): string
    {
        $digits = str_split((string) $value);
        foreach ($digits as $index => $digit) {
            $digits[$index] = self::DIGITS_NP[(int) $digit] ?? $digit;
        }

        return implode('', $digits);
    }
}
