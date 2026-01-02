<?php

declare(strict_types=1);

namespace Nutandc\NepaliDateConverter;

use DateTimeImmutable;
use DateTimeInterface;
use Nutandc\NepaliDateConverter\Exceptions\InvalidDateException;
use Nutandc\NepaliDateConverter\ValueObjects\EnglishDate;
use Nutandc\NepaliDateConverter\ValueObjects\NepaliDate;
use RuntimeException;

final class DateConverter
{
    private const BASE_AD = '1943-12-31';
    private const BASE_BS_YEAR = 2000;
    private const BASE_BS_MONTH = 9;
    private const BASE_BS_DAY = 16;
    private const BASE_BS_DAY_OF_WEEK = 6;
    private const CALENDAR_DATA_PATH = __DIR__ . '/Data/bs_calendar.php';

    private const MIN_AD_YEAR = 1944;
    private const MAX_AD_YEAR = 2033;
    private const MIN_BS_YEAR = 2000;
    private const MAX_BS_YEAR = 2090;

    private const NORMAL_MONTHS = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    private const LEAP_MONTHS = [31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];


    /** @var array<int, array<int, int>> */
    private array $calendarMap = [];
    /** @var array<int, int> */
    private array $bsYearTotals = [];
    /** @var array<int, int> */
    private array $adYearTotals = [];

    private int $baseBsTotal;

    public function __construct()
    {
        $calendarData = $this->loadCalendarData();
        $this->calendarMap = $this->buildCalendarMap($calendarData);
        $this->bsYearTotals = $this->buildBsYearTotals();
        $this->adYearTotals = $this->buildAdYearTotals();
        $this->baseBsTotal = $this->totalNepaliDaysSinceEpoch(
            self::BASE_BS_YEAR,
            self::BASE_BS_MONTH,
            self::BASE_BS_DAY,
        );
    }

    /**
     * Convert a Gregorian (AD) date to Nepali (BS).
     *
     * @throws InvalidDateException
     */
    public function toNepali(int $year, int $month, int $day): NepaliDate
    {
        $this->assertValidEnglishDate($year, $month, $day);

        $totalDays = $this->totalEnglishDaysSinceEpoch($year, $month, $day);

        return $this->calculateNepaliDate($totalDays);
    }

    /**
     * Convert a DateTimeInterface (Carbon, DateTime, etc.) to Nepali (BS).
     *
     * @throws InvalidDateException
     */
    public function toNepaliFromDateTime(DateTimeInterface $date): NepaliDate
    {
        return $this->toNepali(
            (int) $date->format('Y'),
            (int) $date->format('n'),
            (int) $date->format('j'),
        );
    }

    /**
     * Convert a Nepali (BS) date to Gregorian (AD).
     *
     * @throws InvalidDateException
     */
    public function toEnglish(int $year, int $month, int $day): EnglishDate
    {
        $this->assertValidNepaliDate($year, $month, $day);

        $totalDays = $this->totalNepaliDaysSinceEpoch($year, $month, $day);
        $daysFromBase = $totalDays - $this->baseBsTotal;

        $adDate = $this->baseAdDate()->modify(sprintf('+%d days', $daysFromBase));
        $dayOfWeek = $this->englishDayOfWeek($adDate);

        return new EnglishDate(
            (int) $adDate->format('Y'),
            (int) $adDate->format('n'),
            (int) $adDate->format('j'),
            $dayOfWeek,
        );
    }

    public function daysInNepaliMonth(int $year, int $month): int
    {
        $this->assertValidNepaliMonth($year, $month);

        return $this->lookupNepaliMonthDays($year, $month);
    }

    public function daysInEnglishMonth(int $year, int $month): int
    {
        $this->assertValidEnglishMonth($year, $month);

        return $this->lookupEnglishMonthDays($year, $month);
    }

    private function calculateNepaliDate(int $totalEnglishDays): NepaliDate
    {
        $nepaliYear = self::BASE_BS_YEAR;
        $nepaliMonth = self::BASE_BS_MONTH;
        $nepaliDay = self::BASE_BS_DAY;
        $dayOfWeek = self::BASE_BS_DAY_OF_WEEK;

        while ($totalEnglishDays !== 0) {
            $daysInMonth = $this->lookupNepaliMonthDays($nepaliYear, $nepaliMonth);

            $nepaliDay++;
            $dayOfWeek++;

            if ($nepaliDay > $daysInMonth) {
                $nepaliMonth++;
                $nepaliDay = 1;
            }

            if ($dayOfWeek > 7) {
                $dayOfWeek = 1;
            }

            if ($nepaliMonth > 12) {
                $nepaliYear++;
                $nepaliMonth = 1;
            }

            $totalEnglishDays--;
        }

        return new NepaliDate($nepaliYear, $nepaliMonth, $nepaliDay, $dayOfWeek);
    }

    private function totalEnglishDaysSinceEpoch(int $year, int $month, int $day): int
    {
        $totalDays = $this->adYearTotals[$year] ?? 0;

        for ($m = 1; $m < $month; $m++) {
            $totalDays += $this->lookupEnglishMonthDays($year, $m);
        }

        $totalDays += $day;

        return $totalDays;
    }

    private function totalNepaliDaysSinceEpoch(int $year, int $month, int $day): int
    {
        $totalDays = $this->bsYearTotals[$year] ?? 0;

        for ($m = 1; $m < $month; $m++) {
            $totalDays += $this->lookupNepaliMonthDays($year, $m);
        }

        $totalDays += $day;

        return $totalDays;
    }

    private function lookupEnglishMonthDays(int $year, int $month): int
    {
        $months = $this->isLeapYear($year) ? self::LEAP_MONTHS : self::NORMAL_MONTHS;

        return $months[$month - 1] ?? 0;
    }

    private function lookupNepaliMonthDays(int $year, int $month): int
    {
        return $this->calendarMap[$year][$month - 1] ?? 0;
    }

    private function isLeapYear(int $year): bool
    {
        if ($year % 100 === 0) {
            return $year % 400 === 0;
        }

        return $year % 4 === 0;
    }

    private function englishDayOfWeek(DateTimeImmutable $date): int
    {
        return (int) $date->format('w') + 1;
    }

    private function baseAdDate(): DateTimeImmutable
    {
        return new DateTimeImmutable(self::BASE_AD);
    }

    /**
     * @return array<int, array<int, int>>
     */
    private function loadCalendarData(): array
    {
        $data = require self::CALENDAR_DATA_PATH;

        if (! is_array($data) || $data === []) {
            throw new RuntimeException('Nepali calendar data could not be loaded.');
        }

        return $data;
    }

    /**
     * @return array<int, array<int, int>>
     */
    private function buildCalendarMap(array $calendarData): array
    {
        $map = [];
        foreach ($calendarData as $row) {
            $year = (int) $row[0];
            $map[$year] = array_slice($row, 1);
        }

        return $map;
    }

    /**
     * @return array<int, int>
     */
    private function buildBsYearTotals(): array
    {
        $totals = [];
        $total = 0;

        for ($year = self::MIN_BS_YEAR; $year <= self::MAX_BS_YEAR; $year++) {
            if (! isset($this->calendarMap[$year])) {
                throw new RuntimeException(sprintf('Missing BS calendar data for year %d.', $year));
            }

            $totals[$year] = $total;
            $total += array_sum($this->calendarMap[$year]);
        }

        return $totals;
    }

    /**
     * @return array<int, int>
     */
    private function buildAdYearTotals(): array
    {
        $totals = [];
        $total = 0;

        for ($year = self::MIN_AD_YEAR; $year <= self::MAX_AD_YEAR; $year++) {
            $totals[$year] = $total;
            $total += $this->isLeapYear($year) ? 366 : 365;
        }

        return $totals;
    }

    private function assertValidEnglishMonth(int $year, int $month): void
    {
        if ($year < self::MIN_AD_YEAR || $year > self::MAX_AD_YEAR) {
            throw new InvalidDateException(sprintf(
                'English year out of range. Supported years: %d-%d.',
                self::MIN_AD_YEAR,
                self::MAX_AD_YEAR,
            ));
        }

        if ($month < 1 || $month > 12) {
            throw new InvalidDateException('English month must be between 1 and 12.');
        }
    }

    private function assertValidEnglishDate(int $year, int $month, int $day): void
    {
        $this->assertValidEnglishMonth($year, $month);

        if (! checkdate($month, $day, $year)) {
            throw new InvalidDateException('Invalid English date.');
        }
    }

    private function assertValidNepaliMonth(int $year, int $month): void
    {
        if ($year < self::MIN_BS_YEAR || $year > self::MAX_BS_YEAR) {
            throw new InvalidDateException(sprintf(
                'Nepali year out of range. Supported years: %d-%d.',
                self::MIN_BS_YEAR,
                self::MAX_BS_YEAR,
            ));
        }

        if ($month < 1 || $month > 12) {
            throw new InvalidDateException('Nepali month must be between 1 and 12.');
        }
    }

    private function assertValidNepaliDate(int $year, int $month, int $day): void
    {
        $this->assertValidNepaliMonth($year, $month);

        $daysInMonth = $this->lookupNepaliMonthDays($year, $month);
        if ($daysInMonth === 0 || $day < 1 || $day > $daysInMonth) {
            throw new InvalidDateException('Invalid Nepali date.');
        }
    }
}
