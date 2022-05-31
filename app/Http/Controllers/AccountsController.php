<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Domain\Account\AccountAggregateRoot;
use App\Http\Requests\UpdateAccountRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


class AccountsController extends Controller
{
    public function index()
    {
        $accounts = Account::where('user_id', Auth::user()->id)->get();

        return view('accounts.index', compact('accounts'));
    }

    public function store(Request $request)
    {
        $newUuid = Str::uuid()->toString();
        $user_id = mt_rand(1000000, 9999999);
        AccountAggregateRoot::retrieve($newUuid)
            ->createAccount($request->name, $user_id )
            ->persist();

        return response()->json($request->name, 201);
    }

    public function update( UpdateAccountRequest $request)
    {
        $account = Account::name($request->name);

        $aggregateRoot = AccountAggregateRoot::retrieve($account->uuid);

        $request->adding()
            ? $aggregateRoot->addMoney($request->amount)
            : $aggregateRoot->subtractMoney($request->amount);

        $aggregateRoot->persist();

        return response()->json($request->name . 'Balance: '. $aggregateRoot->getBalance() , 200);
    }

    public function destroy(Account $account)
    {
        AccountAggregateRoot::retrieve($account->uuid)
            ->deleteAccount()
            ->persist();

        return back();
    }
}
