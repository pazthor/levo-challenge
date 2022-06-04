<?php

namespace Tests\Domain\Account\Reactors;

use App\Domain\Account\AccountAggregateRoot;
use App\Domain\Account\Events\AccountCreated;
use App\Domain\Account\Events\AccountLimitHit;
use App\Domain\Account\Events\MoneyAdded;
use App\Domain\Account\Events\MoneySubtracted;
use App\Domain\Account\Events\WithdrawExcessiveAmount;
use App\Domain\Account\Exceptions\CouldNotSubtractMoney;
use App\Mail\LoanProposalMail;
use App\Mail\WithdrawExcessiveMail;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotifyWithdrawExcessiveReactorTest extends TestCase
{
    private const ACCOUNT_UUID = 'accounts-uuid';

    private const ACCOUNT_NAME = 'fake-account';
    /** @test */
    public function it_should_sent_email_if_subtract_15k(): void
    {
        Mail::fake();

        $aggregate = AccountAggregateRoot::retrieve($this->account->uuid);

        $aggregate
            ->addMoney(30000)
            ->persist();
        $aggregate->subtractMoney(15000);
        $aggregate->persist();


        Mail::assertSent(function (WithdrawExcessiveMail $mail) {
            return $mail->hasTo('admin@email.com');
        });

    }
}
