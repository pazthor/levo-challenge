<?php

namespace Tests\Domain\Account\Reactors;

use App\Domain\Account\AccountAggregateRoot;
use App\Domain\Account\Exceptions\CouldNotSubtractMoney;
use App\Mail\WithdrawExcessiveMail;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotifyWithdrawExcessiveReactorTest extends TestCase
{
    /** @test */
    public function test_send_withdraw_excessive(): void
    {
        Mail::fake();

        $aggregate = AccountAggregateRoot::retrieve($this->account->uuid)
            ->addMoney(20000)
            ->persist();

        $this->assertExceptionThrown(function () use ($aggregate){
            $aggregate->subtractMoney(10001);
        }, CouldNotSubtractMoney::class);

        Mail::assertSent(function (WithdrawExcessiveMail $mail) {
            return $mail->hasTo('admin@email.com');
        });
    }
}
