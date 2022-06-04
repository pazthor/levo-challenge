<?php

namespace App\Domain\Account\EventQueries;

use App\Domain\Account\Events\MoneyAdded;
use App\Domain\Account\Events\MoneySubtracted;
use App\Domain\Account\Events\WithdrawExcessiveAmount;
use Spatie\EventSourcing\EventHandlers\Projectors\EventQuery;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;
use Illuminate\Support\Collection;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class TransactionFilterEventQuery extends EventQuery
{
    private Collection $transactionsList;

    public function __construct(
        protected string $minDate,
        protected string $agreggateId
    ) {
        $this->transactionsList = collect();

        EloquentStoredEvent::query()
            ->whereEvent(WithdrawExcessiveAmount::class,MoneySubtracted::class,MoneyAdded::class)
            ->whereAggregateRoot($agreggateId)
            ->where('created_at','>=',$this->minDate)
            ->each(
                fn (EloquentStoredEvent $event) => $this->apply($event->toStoredEvent())
            );
    }



    protected function applyMoneySubtracted(
        ShouldBeStored $moneySubtracted
    ) {
        $this->transactionsList->push(
            [
                'datetime'=>$moneySubtracted->createdAt(),
                'amount'=>$moneySubtracted->amount,
                'eventOperation'=>$moneySubtracted->eventOperation,
                'message'=>$moneySubtracted->message
            ]
        );

    }

    public function getLastEvents():array
    {
      return $this->transactionsList->all();
    }

    public function getCountEventsLast48():int
    {
        return $this->transactionsList->count();
    }
}
