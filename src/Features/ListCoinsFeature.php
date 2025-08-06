<?php

namespace Ingenius\Coins\Features;

use Ingenius\Core\Interfaces\FeatureInterface;

class ListCoinsFeature implements FeatureInterface
{
    public function getIdentifier(): string
    {
        return 'list-coins';
    }

    public function getName(): string
    {
        return __('List coins');
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
