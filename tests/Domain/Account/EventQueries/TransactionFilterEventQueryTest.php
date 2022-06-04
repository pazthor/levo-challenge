<?php

namespace Tests\Domain\Account\EventQueries;

use App\Domain\Account\Events\MoneyAdded;
use App\Domain\Account\Events\MoneySubtracted;
use App\Domain\Account\Events\WithdrawExcessiveAmount;
use App\Models\Account;
use App\Domain\Account\AccountAggregateRoot;
use App\Domain\Account\EventQueries\TransactionFilterEventQuery;
use Carbon\Carbon;
use Tests\TestCase;

class TransactionFilterEventQueryTest extends TestCase
{
    /** @test */
    public function it_should_be_get_all_transaction_in_last_48_hours()
    {
        $expectedMessage ="Rejected withdraw because you can not withdraw go above 10,000.";
        $transactionSuspicious ='Warning, transaction suspicious, added more than 10K USD in less than 24 hours';
        $this->assertDatabaseHas((new Account())->getTable(), [
            'user_id' => $this->user->id,
            'uuid' => $this->account->uuid,
        ]);
        $aggregateRoot = AccountAggregateRoot::retrieve($this->account->uuid);
        $last50Hours = Carbon::now()->subHours(50)->toDateTimeString();
        $last23Hours = Carbon::now()->subHours(23)->toDateTimeString();
        $last10Hours = Carbon::now()->subHours(10)->toDateTimeString();
        $last48Hours = Carbon::now()->subHours(48)->toDateTimeString();

        Carbon::setTestNow($last50Hours);

        $aggregateRoot->addMoney(8000)->persist();

        Carbon::setTestNow($last23Hours);
        $aggregateRoot->addMoney(9000)->persist();
        $aggregateRoot->addMoney(9000)->persist();

        Carbon::setTestNow($last10Hours);
        $aggregateRoot = AccountAggregateRoot::retrieve($this->account->uuid);
        $aggregateRoot->subtractMoney(12000);
        $aggregateRoot->persist();

        $this->account->refresh();
        $transactionsLast48Hours = new TransactionFilterEventQuery($last48Hours,$this->account->uuid);

        $countEvent = $transactionsLast48Hours->getCountEventsLast48();


        $arrayTransaction = $transactionsLast48Hours->getLastEvents();
        $firstObject =$arrayTransaction[0];
        $secondObject =$arrayTransaction[1];
        $thirdObject =$arrayTransaction[2];
        $fourthObject =$arrayTransaction[3];

        $this->assertEquals(4, $countEvent );



        $this->assertEquals(9000, $firstObject["amount"]);
        $this->assertEquals(MoneyAdded::class, $firstObject["eventOperation"]);

        $this->assertEquals(9000, $secondObject["amount"]);
        $this->assertEquals(WithdrawExcessiveAmount::class, $secondObject["eventOperation"]);
        $this->assertEquals($transactionSuspicious, $secondObject["message"]);

        $this->assertEquals(9000, $thirdObject["amount"]);
        $this->assertEquals(MoneyAdded::class, $thirdObject["eventOperation"]);


        $this->assertEquals(12000, $fourthObject["amount"]);
        $this->assertEquals(WithdrawExcessiveAmount::class, $fourthObject["eventOperation"]);
        $this->assertEquals( $expectedMessage, $fourthObject["message"]);



    }
}
