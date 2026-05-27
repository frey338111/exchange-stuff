<?php

namespace App\DataProviders\DashboardDataProviders;

use App\Models\Listing;

class LiveListingsDataProvider
{
    public function count(): int
    {
        return Listing::query()
            ->where('status', Listing::STATUS_LIVE)
            ->count();
    }
}
