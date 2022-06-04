<?php

namespace Tests\Domain\Account\EventQueries;

use App\Domain\Account\Events\MoneyAdded;
use App\Domain\Account\Events\MoneySubtracted;
use App\Models\Account;
use App\Domain\Account\AccountAggregateRoot;
use App\Domain\Account\EventQueries\TransactionCountEventQuery;
use Carbon\Carbon;
use Tests\TestCase;

class TransactionCountEventQueryTest extends TestCase
{
    /** @test */
    public function it_should_be_an_excessive_amount_if_15k_is_added()
    {
        $this->assertDatabaseHas((new Account())->getTable(), [
            'user_id' => $this->user->id,
            'uuid' => $this->account->uuid,
        ]);

        AccountAggregateRoot::retrieve($this->account->uuid)
            ->addMoney(15000)
            ->persist();

        $this->account->refresh();

        $last24Hours = Carbon::now()->subHours(24)->toDateTimeString();
        $transactionsLast24Hours = new TransactionCountEventQuery(MoneyAdded::class,$last24Hours,$this->account->uuid);

        $this->assertTrue($transactionsLast24Hours->hasExcessiveAddedMoney(15000));
    }

     /** @test */
    public function it_should_be_an_excessive_amount_if_10k_is_added__in_less_than_24_hours()
     {
        $this->assertDatabaseHas((new Account())->getTable(), [
            'user_id' => $this->user->id,
            'uuid' => $this->account->uuid,
        ]);


        $last20Hours = Carbon::now()->subHours(20)->toDateTimeString();
        $last15Hours = Carbon::now()->subHours(15)->toDateTimeString();
        $last5Hours = Carbon::now()->subHours(5)->toDateTimeString();

        Carbon::setTestNow($last20Hours);
        AccountAggregateRoot::retrieve($this->account->uuid)
        ->addMoney(5000)
        ->persist();

        Carbon::setTestNow($last15Hours);
        AccountAggregateRoot::retrieve($this->account->uuid)
            ->addMoney(3000)
            ->persist();

        Carbon::setTestNow($last5Hours);
        AccountAggregateRoot::retrieve($this->account->uuid)
            ->addMoney(5000)
            ->persist();

        $this->account->refresh();

        $last24Hours = Carbon::now()->subHours(24)->toDateTimeString();
        $transactionsLast24Hours = new TransactionCountEventQuery(MoneyAdded::class,$last24Hours,$this->account->uuid);

        $this->assertTrue($transactionsLast24Hours->hasExcessiveAddedMoney(5000));

        AccountAggregateRoot::retrieve($this->account->uuid)
        ->deleteAccount()
        ->persist();
    }

    /** @test */
    public function it_should_be_not_an_excessive_amount_if_10k_is_added__in_more_than_24_hours()
    {
        Carbon::setTestNow();
        $this->assertDatabaseHas((new Account())->getTable(), [
            'user_id' => $this->user->id,
            'uuid' => $this->account->uuid,
        ]);

        $last30Hours = Carbon::now()->subHours(30)->toDateTimeString();
        $last23Hours = Carbon::now()->subHours(23)->toDateTimeString();
        $last24Hours = Carbon::now()->subHours(24)->toDateTimeString();

        Carbon::setTestNow($last30Hours);
        AccountAggregateRoot::retrieve($this->account->uuid)
            ->addMoney(8000)
            ->persist();

        Carbon::setTestNow($last23Hours);
        AccountAggregateRoot::retrieve($this->account->uuid)
            ->addMoney(9000)
            ->persist();

        $this->account->refresh();

        $transactionsLast24Hours = new TransactionCountEventQuery(MoneyAdded::class,$last24Hours,$this->account->uuid);

        $this->assertEquals(1,$transactionsLast24Hours->getCountEventsLast24Hours());

    }
}
