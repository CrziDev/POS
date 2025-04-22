<?php

use Filament\Notifications\Notification;
use Illuminate\Support\Str;

if (! function_exists('strFormat')) {
    function strFormat() {
        return fn($state) => Str::Headline($state);
    }
}

if (! function_exists('statusColor')) {
    function statusColor($enum) {
        return fn($state) => $enum::getColor($state);
    }
}

if (!function_exists('moneyToNumber')) {
    function moneyToNumber($moneyString): float
    {
        $numericValue = filter_var($moneyString, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        // Convert to float for further calculations
        $numericValue = floatval($numericValue);

        return $numericValue;
    }
}

if (!function_exists('moneyToNumber')) {
    function notification($message)
    {
        return Notification::make()
                ->title($message)
                ->send();
    }
}

