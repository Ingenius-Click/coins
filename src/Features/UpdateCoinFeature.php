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
        return 'Update coin';
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
