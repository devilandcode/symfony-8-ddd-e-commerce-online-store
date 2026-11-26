<?php

declare(strict_types=1);

namespace App\Tests\Cart\Double;

use App\Cart\Application\Interface\CartOwnerProviderInterface;
use App\Cart\Domain\ValueObject\CartOwner;

final class FakeOwnerProvider implements CartOwnerProviderInterface
{
    public int $resolveCalls = 0;

    public function __construct(public ?CartOwner $current)
    {
    }

    public function findCurrent(): ?CartOwner
    {
        return $this->current;
    }

    public function resolveCurrent(): CartOwner
    {
        ++$this->resolveCalls;

        return $this->current ?? throw new \LogicException('No owner.');
    }
}
