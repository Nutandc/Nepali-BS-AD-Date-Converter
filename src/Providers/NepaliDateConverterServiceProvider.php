<?php

declare(strict_types=1);

namespace Nutandc\NepaliDateConverter\Providers;

use Illuminate\Support\ServiceProvider;
use Nutandc\NepaliDateConverter\DateConverter;

final class NepaliDateConverterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DateConverter::class, function () {
            return new DateConverter();
        });

        $this->app->alias(DateConverter::class, 'nepali-date-converter');
    }
}
