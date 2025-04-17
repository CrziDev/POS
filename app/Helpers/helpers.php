<?php

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