<?php

namespace Ingenius\Coins\Policies;

use Ingenius\Coins\Constants\CoinsPermissions;
use Ingenius\Coins\Models\Coin;

class CoinPolicy
{
    public function viewAny($user)
    {
        return true;
    }

    public function view($user, Coin $coin)
    {
        return true;
    }

    public function create($user)
    {
        $userClass = tenant_user_class();

        if ($user && is_object($user) && is_a($user, $userClass)) {
            return $user->can(CoinsPermissions::COINS_CREATE);
        }

        return false;
    }

    public function update($user, Coin $coin)
    {
        $userClass = tenant_user_class();

        if ($user && is_object($user) && is_a($user, $userClass)) {
            return $user->can(CoinsPermissions::COINS_EDIT);
        }

        return false;
    }

    public function delete($user, Coin $coin)
    {
        $userClass = tenant_user_class();

        if ($user && is_object($user) && is_a($user, $userClass)) {
            return $user->can(CoinsPermissions::COINS_DELETE);
        }

        return false;
    }

    public function setMain($user, Coin $coin)
    {
        $userClass = tenant_user_class();

        if ($user && is_object($user) && is_a($user, $userClass)) {
            return $user->can(CoinsPermissions::COINS_SET_MAIN);
        }

        return false;
    }
}
