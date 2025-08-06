<?php

namespace Ingenius\Coins\Features;

use Ingenius\Core\Interfaces\FeatureInterface;

class UpdateCoinFeature implements FeatureInterface
{
    public function getIdentifier(): string
    {
        return 'update-coin';
    }

    public function getName(): string
    {
        return __('Update coin');
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
