<?php

declare(strict_types=1);

namespace Nutandc\NepaliDateConverter\Enums;

enum EnglishDateFormat: string
{
    case DateString = 'date_string';
    case FormattedEnglish = 'formatted_english';
    case Array = 'array';
}
