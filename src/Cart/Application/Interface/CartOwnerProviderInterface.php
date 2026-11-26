<?php

declare(strict_types=1);

namespace App\Cart\Application\Interface;

use App\Cart\Domain\ValueObject\CartOwner;

interface CartOwnerProviderInterface
{
    /** Returns the current visitor's owner identity, or null when none was established yet (read-only, no side effects). */
    public function findCurrent(): ?CartOwner;

    /** Returns the current owner identity, establishing a new guest identity when needed. */
    public function resolveCurrent(): CartOwner;
}
