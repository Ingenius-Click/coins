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
        return __('Delete coin');
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
