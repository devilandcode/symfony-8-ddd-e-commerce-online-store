<?php

declare(strict_types=1);

namespace App\Tests\Cart\Domain;

use App\Cart\Domain\Entity\Cart;
use App\Cart\Domain\Exception\InvalidCartQuantityException;
use App\Cart\Domain\ValueObject\CartOwner;
use PHPUnit\Framework\TestCase;

final class CartTest extends TestCase
{
    public function testNewCartIsEmptyAndKeepsItsOwner(): void
    {
        $owner = CartOwner::user(5);
        $cart = Cart::create($owner, new \DateTime());

        self::assertSame(0, $cart->itemsCount());
        self::assertSame([], $cart->getProductIds());
        self::assertTrue($cart->getOwner()->equals($owner));
    }

    public function testAddingNewProductsCountsDistinctProducts(): void
    {
        $cart = $this->guestCart();

        $cart->addProduct(10, 2, 50, new \DateTime());
        $cart->addProduct(11, 1, 50, new \DateTime());

        self::assertSame(2, $cart->itemsCount());
        self::assertSame([10, 11], $cart->getProductIds());
        self::assertTrue($cart->containsProduct(10));
        self::assertFalse($cart->containsProduct(99));
    }

    public function testAddingExistingProductIncreasesQuantityInsteadOfDuplicatingTheLine(): void
    {
        $cart = $this->guestCart();

        $cart->addProduct(10, 2, 50, new \DateTime());
        $cart->addProduct(10, 3, 50, new \DateTime());

        self::assertSame(1, $cart->itemsCount());
        self::assertSame(5, $cart->getItems()[0]->getQuantity());
    }

    public function testQuantityMustBePositive(): void
    {
        $this->expectException(InvalidCartQuantityException::class);

        $this->guestCart()->addProduct(10, 0, 50, new \DateTime());
    }

    public function testQuantityCannotExceedAvailableStock(): void
    {
        $cart = $this->guestCart();
        $cart->addProduct(10, 2, 3, new \DateTime());

        $this->expectException(InvalidCartQuantityException::class);

        $cart->addProduct(10, 2, 3, new \DateTime());
    }

    public function testQuantityCannotExceedPerProductLimit(): void
    {
        $this->expectException(InvalidCartQuantityException::class);

        $this->guestCart()->addProduct(10, Cart::MAX_QUANTITY_PER_PRODUCT + 1, 1000, new \DateTime());
    }

    public function testRejectedAdditionLeavesCartUnchanged(): void
    {
        $cart = $this->guestCart();
        $cart->addProduct(10, 1, 5, new \DateTime());

        try {
            $cart->addProduct(10, 10, 5, new \DateTime());
            self::fail('Expected quantity rejection.');
        } catch (InvalidCartQuantityException) {
        }

        self::assertSame(1, $cart->getItems()[0]->getQuantity());
    }

    private function guestCart(): Cart
    {
        return Cart::create(CartOwner::guest(hash('sha256', 'guest')), new \DateTime());
    }
}
