<?php

namespace App\Domain\Account\Reactors;

use App\Domain\Account\EventQueries\TransactionFilterEventQuery;
use App\Models\Account;
use App\Domain\Account\Events\WithdrawExcessiveAmount;
use App\Mail\WithdrawExcessiveMail;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Spatie\EventSourcing\EventHandlers\Reactors\Reactor;

class NotifyWithdrawExcessiveReactor extends Reactor implements ShouldQueue
{
    public function __invoke(WithdrawExcessiveAmount $event)
    {
        $admin = 'admin@email.com';
        $aggregateRootUuid = $event->aggregateRootUuid();

        $last24Hours = Carbon::now()->subHours(24)->toDateTimeString();
        $eq = new TransactionFilterEventQuery( $last24Hours,$aggregateRootUuid);

        $eventlast48hours = $eq->getLastEvents();

        Mail::to($admin)->send(new WithdrawExcessiveMail());
    }
}
