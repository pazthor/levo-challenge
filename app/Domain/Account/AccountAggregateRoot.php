<?php

namespace App\Domain\Account;

use App\Domain\Account\Events\AccountCreated;
use App\Domain\Account\Events\AccountDeleted;
use App\Domain\Account\Events\AccountLimitHit;
use App\Domain\Account\EventQueries\TransactionCountEventQuery;
use App\Domain\Account\EventQueries\TransactionFilterEventQuery;
use App\Domain\Account\Events\MoneyAdded;
use App\Domain\Account\Events\MoneySubtracted;
use App\Domain\Account\Events\MoreMoneyNeeded;
use App\Domain\Account\Events\WithdrawExcessiveAmount;
use App\Domain\Account\Exceptions\CouldNotSubtractMoney;
use Carbon\Carbon;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;

class AccountAggregateRoot extends AggregateRoot
{
    protected int $balance = 0;

    protected int $accountLimit = 0;

    protected int $limitTransaction = 0;

    protected int $accountLimitHitInARow = 0;


    public function createAccount(string $name, string $userId)
    {
        $this->recordThat(new AccountCreated($name, $userId));

        return $this;
    }

    public function addMoney(int $amount)
    {
        $this->recordThat(new MoneyAdded($amount));

        return $this;
    }

    public function applyMoneyAdded(MoneyAdded $event)
    {
        $this->accountLimitHitInARow = 0;

        $this->balance += $event->amount;
    }

    public function subtractMoney(int $amount)
    {
        $last24Hours = Carbon::now()->subHours(24)->toDateTimeString();
        $transactionEventQuery = new TransactionCountEventQuery(MoneySubtracted::class, $last24Hours,$this->uuid()) ;

        if (!$this->hasSufficientFundsToSubtractAmount($amount)) {
            $this->recordThat(new AccountLimitHit());

            if ($this->needsMoreMoney()) {
                $this->recordThat(new MoreMoneyNeeded());
            }

            $this->persist();

            throw CouldNotSubtractMoney::notEnoughFunds($amount);
        }

        if ($transactionEventQuery->hasExcessiveMoneySubtracted($amount)) {
            $this->recordThat(new WithdrawExcessiveAmount($amount));

            $this->persist();
            return $this;
        }


        $this->recordThat(new MoneySubtracted($amount));
        return $this;
    }

    public function applyMoneySubtracted(MoneySubtracted $event)
    {
        $this->balance -= $event->amount;

        $this->accountLimitHitInARow = 0;
    }

    public function deleteAccount()
    {
        $this->recordThat(new AccountDeleted());

        return $this;
    }

    public function applyAccountLimitHit(AccountLimitHit $accountLimitHit)
    {
        $this->accountLimitHitInARow++;
    }

    private function hasSufficientFundsToSubtractAmount(int $amount): bool
    {
        return $this->balance - $amount >= $this->accountLimit;
    }



    private function needsMoreMoney()
    {
        return $this->accountLimitHitInARow >= 3;
    }

    public function getBalance(){
        return $this->balance;
    }
}
