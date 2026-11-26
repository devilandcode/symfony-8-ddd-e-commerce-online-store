<?php

declare(strict_types=1);

namespace App\Tests\Cart\Domain;

use App\Cart\Domain\ValueObject\CartOwner;
use PHPUnit\Framework\TestCase;

final class CartOwnerTest extends TestCase
{
    public function testGuestOwnerRequiresSha256HexToken(): void
    {
        $token = hash('sha256', 'secret');
        $owner = CartOwner::guest($token);

        self::assertTrue($owner->isGuest());
        self::assertSame($token, $owner->getGuestToken());
        self::assertNull($owner->getUserId());
        self::assertTrue($owner->equals(CartOwner::guest($token)));
    }

    public function testGuestOwnerRejectsMalformedToken(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        CartOwner::guest('not-a-token');
    }

    public function testUserOwnerRequiresPositiveId(): void
    {
        self::assertSame(7, CartOwner::user(7)->getUserId());
        self::assertFalse(CartOwner::user(7)->equals(CartOwner::guest(hash('sha256', 'x'))));

        $this->expectException(\InvalidArgumentException::class);

        CartOwner::user(0);
    }
}
