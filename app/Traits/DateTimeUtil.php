<?php

namespace App\Traits;

trait DateTimeUtil
{
    private function dateBucket($date): string
    {
        if (! $date) {
            return 'older';
        }

        $hours = $date->diffInHours(now());

        return match (true) {
            $hours <= 24 => '24h',
            $hours <= 24 * 7 => 'week',
            $hours <= 24 * 30 => 'month',
            default => 'older',
        };
    }
}
