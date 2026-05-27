<?php

namespace App\ViewModels;

use App\DataProviders\DashboardDataProviders\LiveListingsDataProvider;
use App\DataProviders\DashboardDataProviders\PendingListingsDataProvider;
use App\DataProviders\DashboardDataProviders\RegisteredCustomersDataProvider;
use Illuminate\Contracts\Support\Arrayable;

class DashboardViewModel implements Arrayable
{
    public function __construct(
        private readonly PendingListingsDataProvider $pendingListingsDataProvider,
        private readonly LiveListingsDataProvider $liveListingsDataProvider,
        private readonly RegisteredCustomersDataProvider $registeredCustomersDataProvider,
    ) {
    }

    public function toArray(): array
    {
        return [
            'dashboardCards' => [
                [
                    'label' => __('Pending Listings'),
                    'value' => (string) $this->pendingListingsDataProvider->count(),
                    'note' => __('Awaiting review'),
                    'noteClass' => 'text-yellow-700 dark:text-yellow-300',
                ],
                [
                    'label' => __('Live Listings'),
                    'value' => (string) $this->liveListingsDataProvider->count(),
                    'note' => __('Available in storefront'),
                    'noteClass' => 'text-green-700 dark:text-green-300',
                ],
                [
                    'label' => __('Registered Customers'),
                    'value' => (string) $this->registeredCustomersDataProvider->count(),
                    'note' => __(':day past day / :week past week', [
                        'day' => $this->registeredCustomersDataProvider->registeredPastDayCount(),
                        'week' => $this->registeredCustomersDataProvider->registeredPastWeekCount(),
                    ]),
                    'noteClass' => 'text-indigo-700 dark:text-indigo-300',
                ],
                [
                    'label' => __('Total Payouts'),
                    'value' => '$8,420',
                    'note' => __('Dummy month-to-date value'),
                    'noteClass' => 'text-gray-600 dark:text-gray-300',
                ],
            ],
        ];
    }
}
