<?php

namespace Ingenius\Coins\Features;

use Ingenius\Core\Interfaces\FeatureInterface;

class SetMainCoinFeature implements FeatureInterface
{
    public function getIdentifier(): string
    {
        return 'set-main-coin';
    }

    public function getName(): string
    {
        return __('Set main coin');
    }

    public function getGroup(): string
    {
        return __('Coins');
    }

    public function getPackage(): string
    {
        return 'coins';
    }

    public function isBasic(): bool
    {
        return true;
    }
}
