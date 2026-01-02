# Nepali BS-AD Date Converter

A lightweight PHP package to convert Nepali Bikram Sambat (BS) dates to Gregorian (AD) and vice versa.

## Requirements

- PHP 8.2+
- Laravel 10+ (optional, for container binding)

## Installation

```bash
composer require nutandc/nepali-date-converter
```

## Usage

```php
use Nutandc\NepaliDateConverter\DateConverter;

$converter = new DateConverter();

// AD to BS
$bs = $converter->toNepali(2020, 10, 4);

$bs->toDateString();
// 2077-06-18

$bs->toFormattedEnglish();
// 18 Ashoj 2077, Sunday

$bs->toFormattedNepali();
// २०७७ असोज १८, आइतवार

$bs->toArray();
// ['year' => 2077, 'month' => 6, 'day' => 18, 'day_of_week' => 1]

$bs->toFormattedArray();
// ['year' => '२०७७', 'month' => 'असोज', 'day' => '१८', 'day_of_week' => 'आइतवार']

// AD to BS using DateTime/Carbon
$bs = $converter->toNepaliFromDateTime(new DateTimeImmutable('2022-09-08'));

// BS to AD
$ad = $converter->toEnglish(2077, 6, 18);

$ad->toDateString();
// 2020-10-04

$ad->toFormattedEnglish();
// October 4, 2020
```

## Helpers

```php
$converter->daysInNepaliMonth(2077, 6); // 30
$converter->daysInEnglishMonth(2020, 2); // 29
```

## Laravel 10+ Usage

```php
use Nutandc\NepaliDateConverter\DateConverter;

$converter = app(DateConverter::class);
$bs = $converter->toNepali(2020, 10, 4);
```

## Supported Range

- AD: 1944 - 2033
- BS: 2000 - 2090

## License

MIT
