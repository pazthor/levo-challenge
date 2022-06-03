<?php

namespace App\Domain\Account\EventQueries;

use App\Domain\Account\Events\MoneyAdded;
use Spatie\EventSourcing\EventHandlers\Projectors\EventQuery;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;

class TransactionCountEventQuery extends EventQuery
{
    private int $balance = 0;
    public function __construct(
        protected string $minDate,
        protected string $agreggateId
    ) {
        EloquentStoredEvent::query()
            ->whereEvent(MoneyAdded::class)
            ->whereAggregateRoot($agreggateId)
            ->where('created_at','>=',$this->minDate)
            ->each(
                fn (EloquentStoredEvent $event) => $this->apply($event->toStoredEvent())
            );
    }

    protected function applyMoneyAdded(
        MoneyAdded $moneyAdded
    ) {
        $this->balance += $moneyAdded->amount;

    }

    public function isExcessiveAmount(): bool
    {
        return $this->balance >=10000;
    }


    public function amount(): int
    {
        return $this->balance;
    }

}
