<?php

namespace App\DataProviders\DashboardDataProviders;

use App\Models\Customer;

class RegisteredCustomersDataProvider
{
    public function count(): int
    {
        return Customer::query()->count();
    }

    public function registeredPastDayCount(): int
    {
        return Customer::query()
            ->where('created_at', '>=', now()->subDay())
            ->count();
    }

    public function registeredPastWeekCount(): int
    {
        return Customer::query()
            ->where('created_at', '>=', now()->subWeek())
            ->count();
    }
}
