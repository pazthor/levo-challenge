<?php

namespace App\Domain\Account\EventQueries;

use App\Domain\Account\Events\MoneyAdded;
use App\Domain\Account\Events\MoneySubtracted;
use Spatie\EventSourcing\EventHandlers\Projectors\EventQuery;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class TransactionCountEventQuery extends EventQuery
{
    private int $balance = 0;
    private int $countTransactions = 0;

    public function __construct(
        protected string $eventName,
        protected string $minDate,
        protected string $agreggateId
    ) {
        EloquentStoredEvent::query()
            ->whereEvent($eventName)
            ->whereAggregateRoot($agreggateId)
            ->where('created_at','>=',$this->minDate)
            ->each(
                fn (EloquentStoredEvent $event) => $this->apply($event->toStoredEvent())
            );
    }

    protected function applyMoneyAdded(
        ShouldBeStored $moneyAdded
    ) {
        $this->balance += $moneyAdded->amount;
        $this->countTransactions++;
    }

    public function hasExcessiveMoneySubtracted(int $amount): bool
    {
        return $this->balance + $amount >= 10000;
    }

    public function hasExcessiveAddedMoney(int $amount): bool
    {
        return $this->balance + $amount>= 10000;
    }


    public function amount(): int
    {
        return $this->balance;
    }

    public function getCountEventsLast24Hours():int
    {
        return $this->countTransactions;
    }

}
