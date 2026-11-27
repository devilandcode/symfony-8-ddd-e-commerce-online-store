<?php

declare(strict_types=1);

namespace App\Tests\Cart\Domain;

use App\Cart\Domain\Entity\Cart;
use App\Cart\Domain\Exception\InvalidCartQuantityException;
use App\Cart\Domain\Exception\ProductNotInCartException;
use App\Cart\Domain\ValueObject\CartOwner;
use App\Cart\Domain\ValueObject\Quantity;
use App\Cart\Domain\ValueObject\StockLimit;
use PHPUnit\Framework\TestCase;

final class CartTest extends TestCase
{
    public function testNewCartIsEmptyAndKeepsItsOwner(): void
    {
        $owner = CartOwner::user(5);
        $cart = Cart::create($owner, new \DateTime());

        self::assertTrue($cart->isEmpty());
        self::assertSame(0, $cart->getTotalQuantity());
        self::assertSame([], $cart->getProductIds());
        self::assertTrue($cart->getOwner()->equals($owner));
    }

    public function testTotalQuantityIsTheSumOfQuantitiesNotTheNumberOfLines(): void
    {
        $cart = $this->guestCart();

        $cart->addProduct(10, Quantity::of(2), StockLimit::ofUnits(50), new \DateTime());
        $cart->addProduct(11, Quantity::of(1), StockLimit::ofUnits(50), new \DateTime());

        self::assertSame(3, $cart->getTotalQuantity());
        self::assertCount(2, $cart->getItems());
        self::assertSame([10, 11], $cart->getProductIds());
        self::assertTrue($cart->containsProduct(10));
        self::assertFalse($cart->containsProduct(99));
    }

    public function testAddingExistingProductIncreasesQuantityInsteadOfDuplicatingTheLine(): void
    {
        $cart = $this->guestCart();

        $cart->addProduct(10, Quantity::of(2), StockLimit::ofUnits(50), new \DateTime());
        $cart->addProduct(10, Quantity::of(3), StockLimit::ofUnits(50), new \DateTime());

        self::assertSame(5, $cart->getTotalQuantity());
        self::assertCount(1, $cart->getItems());
        self::assertSame(5, $cart->getItems()[0]->getQuantity()->getValue());
    }

    public function testQuantityCannotExceedAvailableStock(): void
    {
        $cart = $this->guestCart();
        $cart->addProduct(10, Quantity::of(2), StockLimit::ofUnits(3), new \DateTime());

        $this->expectException(InvalidCartQuantityException::class);

        $cart->addProduct(10, Quantity::of(2), StockLimit::ofUnits(3), new \DateTime());
    }

    public function testQuantityIsLimitedOnlyByStockNotByAFixedCap(): void
    {
        $cart = $this->guestCart();

        $cart->addProduct(10, Quantity::of(150), StockLimit::ofUnits(150), new \DateTime());

        self::assertSame(150, $cart->getItems()[0]->getQuantity()->getValue());
    }

    public function testChangeQuantitySetsTheExactQuantity(): void
    {
        $cart = $this->guestCart();
        $cart->addProduct(10, Quantity::of(2), StockLimit::ofUnits(50), new \DateTime());

        $cart->changeQuantity(10, Quantity::of(5), StockLimit::ofUnits(50), new \DateTime());
        self::assertSame(5, $cart->getItems()[0]->getQuantity()->getValue());

        $cart->changeQuantity(10, Quantity::of(1), StockLimit::ofUnits(50), new \DateTime());
        self::assertSame(1, $cart->getItems()[0]->getQuantity()->getValue());
    }

    public function testChangeQuantityAllowsExactlyTheStockBalance(): void
    {
        $cart = $this->guestCart();
        $cart->addProduct(10, Quantity::of(1), StockLimit::ofUnits(4), new \DateTime());

        $cart->changeQuantity(10, Quantity::of(4), StockLimit::ofUnits(4), new \DateTime());

        self::assertSame(4, $cart->getItems()[0]->getQuantity()->getValue());
    }

    public function testChangeQuantityCannotExceedStockAndKeepsTheOldQuantity(): void
    {
        $cart = $this->guestCart();
        $cart->addProduct(10, Quantity::of(2), StockLimit::ofUnits(4), new \DateTime());

        try {
            $cart->changeQuantity(10, Quantity::of(5), StockLimit::ofUnits(4), new \DateTime());
            self::fail('Expected quantity rejection.');
        } catch (InvalidCartQuantityException) {
        }

        self::assertSame(2, $cart->getItems()[0]->getQuantity()->getValue());
    }

    public function testChangeQuantityOfAProductNotInTheCartIsRejected(): void
    {
        $this->expectException(ProductNotInCartException::class);

        $this->guestCart()->changeQuantity(10, Quantity::of(1), StockLimit::ofUnits(4), new \DateTime());
    }

    public function testTotalQuantityFollowsChangesAndRemovals(): void
    {
        $cart = $this->guestCart();
        $cart->addProduct(10, Quantity::of(2), StockLimit::ofUnits(50), new \DateTime());
        $cart->addProduct(11, Quantity::of(4), StockLimit::ofUnits(50), new \DateTime());

        $cart->changeQuantity(10, Quantity::of(7), StockLimit::ofUnits(50), new \DateTime());
        self::assertSame(11, $cart->getTotalQuantity());

        $cart->removeProduct(11, new \DateTime());
        self::assertSame(7, $cart->getTotalQuantity());
    }

    public function testMutationsTouchUpdatedAt(): void
    {
        $cart = $this->guestCart();
        $later = new \DateTime('+1 hour');

        $cart->addProduct(10, Quantity::of(1), StockLimit::ofUnits(5), $later);

        self::assertSame($later, $cart->getUpdatedAt());
    }

    public function testRejectedChangeDoesNotTouchUpdatedAt(): void
    {
        $created = new \DateTime('2026-01-01');
        $cart = Cart::create(CartOwner::guest(hash('sha256', 'guest')), $created);

        try {
            $cart->addProduct(10, Quantity::of(9), StockLimit::ofUnits(5), new \DateTime('2026-02-01'));
            self::fail('Expected quantity rejection.');
        } catch (InvalidCartQuantityException) {
        }

        self::assertSame($created, $cart->getUpdatedAt());
        self::assertTrue($cart->isEmpty());
    }

    public function testRemoveProductDropsOnlyThatLine(): void
    {
        $cart = $this->guestCart();
        $cart->addProduct(10, Quantity::of(2), StockLimit::ofUnits(50), new \DateTime());
        $cart->addProduct(11, Quantity::of(1), StockLimit::ofUnits(50), new \DateTime());

        $cart->removeProduct(10, new \DateTime());

        self::assertSame([11], $cart->getProductIds());
        self::assertFalse($cart->containsProduct(10));
    }

    public function testRemoveProductNotInTheCartIsRejected(): void
    {
        $this->expectException(ProductNotInCartException::class);

        $this->guestCart()->removeProduct(10, new \DateTime());
    }

    public function testRejectedAdditionLeavesCartUnchanged(): void
    {
        $cart = $this->guestCart();
        $cart->addProduct(10, Quantity::of(1), StockLimit::ofUnits(5), new \DateTime());

        try {
            $cart->addProduct(10, Quantity::of(10), StockLimit::ofUnits(5), new \DateTime());
            self::fail('Expected quantity rejection.');
        } catch (InvalidCartQuantityException) {
        }

        self::assertSame(1, $cart->getItems()[0]->getQuantity()->getValue());
    }

    private function guestCart(): Cart
    {
        return Cart::create(CartOwner::guest(hash('sha256', 'guest')), new \DateTime());
    }
}
