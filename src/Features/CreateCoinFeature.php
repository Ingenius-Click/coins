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
        return 'Create coin';
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
