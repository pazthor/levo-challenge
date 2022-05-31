<?php

namespace App\Domain\Account\Exceptions;

use DomainException;

class CouldNotSubtractMoney extends DomainException
{
    public static function notEnoughFunds(int $amount): self
    {
        return new static("Could not subtract amount {$amount} because you can not go below 0.");
    }

    public static function notAllowedWithdrawAmount(int $amount): self
    {
        return new static("You could not withdraw the amount of {$amount} because you can not withdraw go above 10,000.");
    }
}
