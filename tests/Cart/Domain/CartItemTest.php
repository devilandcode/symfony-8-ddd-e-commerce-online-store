<?php

declare(strict_types=1);

namespace App\Tests\Cart\Domain;

use App\Cart\Domain\Entity\Cart;
use App\Cart\Domain\Entity\CartItem;
use App\Cart\Domain\Exception\InvalidCartQuantityException;
use App\Cart\Domain\ValueObject\CartOwner;
use App\Cart\Domain\ValueObject\Quantity;
use App\Cart\Domain\ValueObject\StockLimit;
use PHPUnit\Framework\TestCase;

final class CartItemTest extends TestCase
{
    public function testNewLineKeepsProductAndQuantity(): void
    {
        $item = $this->item(10, 2, 5);

        self::assertSame(10, $item->getProductId());
        self::assertSame(2, $item->getQuantity()->getValue());
    }

    public function testNewLineCannotStartAboveStock(): void
    {
        $this->expectException(InvalidCartQuantityException::class);

        $this->item(10, 6, 5);
    }

    public function testIncreaseByAddsToTheCurrentQuantity(): void
    {
        $item = $this->item(10, 2, 5);

        $item->increaseBy(Quantity::of(3), StockLimit::ofUnits(5));

        self::assertSame(5, $item->getQuantity()->getValue());
    }

    public function testIncreaseAboveStockIsRejectedAndKeepsTheQuantity(): void
    {
        $item = $this->item(10, 2, 5);

        try {
            $item->increaseBy(Quantity::of(4), StockLimit::ofUnits(5));
            self::fail('Expected quantity rejection.');
        } catch (InvalidCartQuantityException) {
        }

        self::assertSame(2, $item->getQuantity()->getValue());
    }

    public function testChangeToReplacesTheQuantityWithinStock(): void
    {
        $item = $this->item(10, 4, 5);

        $item->changeTo(Quantity::of(1), StockLimit::ofUnits(5));
        self::assertSame(1, $item->getQuantity()->getValue());

        $item->changeTo(Quantity::of(5), StockLimit::ofUnits(5));
        self::assertSame(5, $item->getQuantity()->getValue());
    }

    public function testChangeToAboveStockIsRejectedAndKeepsTheQuantity(): void
    {
        $item = $this->item(10, 4, 5);

        try {
            $item->changeTo(Quantity::of(6), StockLimit::ofUnits(5));
            self::fail('Expected quantity rejection.');
        } catch (InvalidCartQuantityException) {
        }

        self::assertSame(4, $item->getQuantity()->getValue());
    }

    private function item(int $productId, int $quantity, int $stock): CartItem
    {
        $cart = Cart::create(CartOwner::guest(hash('sha256', 'guest')), new \DateTime());

        return new CartItem($cart, $productId, Quantity::of($quantity), StockLimit::ofUnits($stock));
    }
}
