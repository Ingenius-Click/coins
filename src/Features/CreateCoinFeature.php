<?php

namespace Ingenius\Coins\Features;

use Ingenius\Core\Interfaces\FeatureInterface;

class CreateCoinFeature implements FeatureInterface
{
    public function getIdentifier(): string
    {
        return 'create-coin';
    }

    public function getName(): string
    {
        return __('Create coin');
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
