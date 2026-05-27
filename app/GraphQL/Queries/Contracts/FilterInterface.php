<?php

namespace App\GraphQL\Queries\Contracts;

interface FilterInterface
{
    public const DATE_FILTERS = [
        '24h' => 'In 24hrs',
        'week' => 'Last week',
        'month' => 'Last month',
        'older' => '1 month +',
    ];
}
