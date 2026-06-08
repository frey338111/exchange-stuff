<?php

namespace App\Providers;

use App\Events\ClaimRequestAccepted;
use App\Events\ClaimRequestAmended;
use App\Events\ClaimRequestCreated;
use App\Events\ClaimRequestRejected;
use App\Jobs\SendClaimRequestCreatedEmail;
use App\Jobs\SendRequestAmendEmail;
use App\Jobs\SendRequestApprovalEmail;
use App\Jobs\SendRequestRejectEmail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(ClaimRequestCreated::class, function (ClaimRequestCreated $event): void {
            SendClaimRequestCreatedEmail::dispatch($event->requestId, $event->message);
        });

        Event::listen(ClaimRequestAccepted::class, function (ClaimRequestAccepted $event): void {
            SendRequestApprovalEmail::dispatch($event->requestId);
        });

        Event::listen(ClaimRequestRejected::class, function (ClaimRequestRejected $event): void {
            SendRequestRejectEmail::dispatch($event->requestId);
        });

        Event::listen(ClaimRequestAmended::class, function (ClaimRequestAmended $event): void {
            SendRequestAmendEmail::dispatch($event->requestId);
        });
    }
}
