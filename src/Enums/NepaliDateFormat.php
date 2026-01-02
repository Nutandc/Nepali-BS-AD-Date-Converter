<?php

declare(strict_types=1);

namespace Nutandc\NepaliDateConverter\Enums;

enum NepaliDateFormat: string
{
    case DateString = 'date_string';
    case FormattedEnglish = 'formatted_english';
    case FormattedNepali = 'formatted_nepali';
    case Array = 'array';
    case FormattedArray = 'formatted_array';
}
