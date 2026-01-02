<?php

declare(strict_types=1);

namespace Tests;

use Nutandc\NepaliDateConverter\DateConverter;
use Nutandc\NepaliDateConverter\Exceptions\InvalidDateException;
use PHPUnit\Framework\TestCase;

final class DateConverterTest extends TestCase
{
    public function test_converts_ad_to_bs(): void
    {
        $converter = new DateConverter();
        $bs = $converter->toNepali(2020, 10, 4);

        $this->assertSame('2077-06-18', $bs->toDateString());
        $this->assertSame('18 Ashoj 2077, Sunday', $bs->toFormattedEnglish());
    }

    public function test_converts_bs_to_ad(): void
    {
        $converter = new DateConverter();
        $ad = $converter->toEnglish(2077, 6, 18);

        $this->assertSame('2020-10-04', $ad->toDateString());
        $this->assertSame('October 4, 2020', $ad->toFormattedEnglish());
    }

    public function test_invalid_english_date_throws(): void
    {
        $converter = new DateConverter();

        $this->expectException(InvalidDateException::class);
        $converter->toNepali(1900, 1, 1);
    }

    public function test_invalid_nepali_date_throws(): void
    {
        $converter = new DateConverter();

        $this->expectException(InvalidDateException::class);
        $converter->toEnglish(2091, 1, 1);
    }

    public function test_days_in_month_helpers(): void
    {
        $converter = new DateConverter();

        $this->assertSame(29, $converter->daysInEnglishMonth(2020, 2));
        $this->assertSame(30, $converter->daysInNepaliMonth(2077, 6));
    }

    public function test_round_trip_ad_to_bs_to_ad(): void
    {
        $converter = new DateConverter();
        mt_srand(42);

        for ($i = 0; $i < 20; $i++) {
            $year = mt_rand(1944, 2033);
            $month = mt_rand(1, 12);
            $daysInMonth = (int) (new \DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month)))->format('t');
            $day = mt_rand(1, $daysInMonth);

            $bs = $converter->toNepali($year, $month, $day);
            $ad = $converter->toEnglish($bs->year, $bs->month, $bs->day);

            $this->assertSame(sprintf('%04d-%02d-%02d', $year, $month, $day), $ad->toDateString());
        }
    }

    public function test_round_trip_bs_to_ad_to_bs(): void
    {
        $converter = new DateConverter();
        mt_srand(84);

        for ($i = 0; $i < 20; $i++) {
            $year = mt_rand(2000, 2090);
            $month = mt_rand(1, 12);
            $day = mt_rand(1, $converter->daysInNepaliMonth($year, $month));

            $ad = $converter->toEnglish($year, $month, $day);
            $bs = $converter->toNepali($ad->year, $ad->month, $ad->day);

            $this->assertSame(sprintf('%04d-%02d-%02d', $year, $month, $day), $bs->toDateString());
        }
    }

    public function test_boundary_dates_round_trip(): void
    {
        $converter = new DateConverter();

        $minAd = $converter->toNepali(1944, 1, 1);
        $this->assertSame('1944-01-01', $converter->toEnglish($minAd->year, $minAd->month, $minAd->day)->toDateString());

        $maxAd = $converter->toNepali(2033, 12, 31);
        $this->assertSame('2033-12-31', $converter->toEnglish($maxAd->year, $maxAd->month, $maxAd->day)->toDateString());
    }
}
