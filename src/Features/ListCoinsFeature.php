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
        return 'List coins';
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
