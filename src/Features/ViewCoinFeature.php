<?php

namespace Ingenius\Coins\Features;

use Ingenius\Core\Interfaces\FeatureInterface;

class ViewCoinFeature implements FeatureInterface
{
    public function getIdentifier(): string
    {
        return 'view-coin';
    }

    public function getName(): string
    {
        return __('View coin');
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
