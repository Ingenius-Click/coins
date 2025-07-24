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
        return 'View coin';
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
