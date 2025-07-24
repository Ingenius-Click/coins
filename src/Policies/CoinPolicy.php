<?php

namespace Ingenius\Coins\Policies;

use Ingenius\Auth\Models\User;
use Ingenius\Coins\Constants\CoinsPermissions;
use Ingenius\Coins\Models\Coin;

class CoinPolicy
{
    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Coin $coin)
    {
        return true;
    }

    public function create(User $user)
    {
        return $user->can(CoinsPermissions::COINS_CREATE);
    }

    public function update(User $user, Coin $coin)
    {
        return $user->can(CoinsPermissions::COINS_EDIT);
    }

    public function delete(User $user, Coin $coin)
    {
        return $user->can(CoinsPermissions::COINS_DELETE);
    }

    public function setMain(User $user, Coin $coin)
    {
        return $user->can(CoinsPermissions::COINS_SET_MAIN);
    }
}
