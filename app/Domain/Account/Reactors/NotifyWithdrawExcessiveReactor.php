<?php

namespace App\Domain\Account\Reactors;

use App\Models\Account;
use App\Domain\Account\Events\WithdrawExcessiveAmount;
use App\Mail\WithdrawExcessiveMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Spatie\EventSourcing\EventHandlers\Reactors\Reactor;

class NotifyWithdrawExcessiveReactor extends Reactor implements ShouldQueue
{
    public function __invoke(WithdrawExcessiveAmount $event)
    {
        $admin = "admin@email.com";

        Mail::to($admin)->send(new WithdrawExcessiveMail());
    }
}
