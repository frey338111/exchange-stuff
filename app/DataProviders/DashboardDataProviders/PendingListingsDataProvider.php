<?php

namespace App\DataProviders\DashboardDataProviders;

use App\Models\Listing;

class PendingListingsDataProvider
{
    public function count(): int
    {
        return Listing::query()
            ->where('status', Listing::STATUS_PENDING)
            ->count();
    }
}
