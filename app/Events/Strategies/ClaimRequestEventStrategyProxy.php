<?php

namespace App\Events\Strategies;

use App\Models\ClaimRequest;
use InvalidArgumentException;

class ClaimRequestEventStrategyProxy implements ClaimRequestEventStrategy
{
    private ?ClaimRequestEventStrategy $strategy = null;

    /**
     * @param class-string<ClaimRequestEventStrategy> $strategyClass
     */
    public function __construct(private readonly string $strategyClass)
    {
    }

    public function dispatch(ClaimRequest $claimRequest, string $message, array $rejectedRequestIds): void
    {
        $this->strategy()->dispatch($claimRequest, $message, $rejectedRequestIds);
    }

    private function strategy(): ClaimRequestEventStrategy
    {
        if ($this->strategy !== null) {
            return $this->strategy;
        }

        $strategy = app($this->strategyClass);

        if (! $strategy instanceof ClaimRequestEventStrategy) {
            throw new InvalidArgumentException(sprintf(
                '%s must implement %s.',
                $this->strategyClass,
                ClaimRequestEventStrategy::class,
            ));
        }

        return $this->strategy = $strategy;
    }
}
