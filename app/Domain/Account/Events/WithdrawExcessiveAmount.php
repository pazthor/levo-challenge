<?php

namespace App\Domain\Account\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class WithdrawExcessiveAmount extends ShouldBeStored
{
    public function __construct(
        public int $amount,
        public string $message="Rejected withdraw because you can not withdraw go above 10,000.",
        public string $eventOperation=WithdrawExcessiveAmount::class,
    ) {
    }
}
