<?php

namespace Ingenius\Coins\Features;

use Ingenius\Core\Interfaces\FeatureInterface;

class DeleteCoinFeature implements FeatureInterface
{
    public function getIdentifier(): string
    {
        return 'delete-coin';
    }

    public function getName(): string
    {
        return 'Delete coin';
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
