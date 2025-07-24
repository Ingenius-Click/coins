<?php

namespace Ingenius\Coins\Enums;

enum CoinPosition: string
{
    case FRONT = 'front';
    case BACK = 'back';

    /**
     * Get the string value of the enum
     */
    public function toString(): string
    {
        return $this->value;
    }
}
