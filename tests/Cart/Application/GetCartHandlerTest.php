<?php

declare(strict_types=1);

namespace App\Tests\Cart\Application;

use App\Cart\Application\FrontUseCases\GetCart\GetCartHandler;
use App\Cart\Application\FrontUseCases\Output\CartProductDTO;
use App\Cart\Domain\Entity\Cart;
use App\Cart\Domain\ValueObject\CartOwner;
use App\Cart\Domain\ValueObject\Quantity;
use App\Cart\Domain\ValueObject\StockLimit;
use App\Tests\Cart\Double\FakeCartProductProvider;
use App\Tests\Cart\Double\FakeOwnerProvider;
use App\Tests\Cart\Double\InMemoryCartRepository;
use PHPUnit\Framework\TestCase;

final class GetCartHandlerTest extends TestCase
{
    private InMemoryCartRepository $carts;
    private FakeOwnerProvider $owners;
    private CartOwner $owner;

    protected function setUp(): void
    {
        $this->owner = CartOwner::guest(hash('sha256', 'guest'));
        $this->carts = new InMemoryCartRepository();
        $this->owners = new FakeOwnerProvider($this->owner);
    }

    public function testVisitorWithoutOwnerOrCartGetsAnEmptyCart(): void
    {
        $products = new FakeCartProductProvider([]);

        $this->owners->current = null;
        self::assertTrue($this->handler($products)()->isEmpty());

        $this->owners->current = $this->owner;
        self::assertTrue($this->handler($products)()->isEmpty());
        self::assertSame(0, $products->calls, 'Catalog must not be queried for an empty cart.');
    }

    public function testBuildsLinesAndTotalsInMinorUnitsWithOneCatalogQuery(): void
    {
        $this->addToCart([10 => 2, 11 => 1]);
        $products = new FakeCartProductProvider([
            10 => new CartProductDTO(10, 'Perfume', 'Burberry', 'a.jpg', 10000, 20000, 'BYN', 5),
            11 => new CartProductDTO(11, 'Cream', 'Dior', null, 550, null, 'BYN', 5),
        ]);

        $cart = $this->handler($products)();

        self::assertSame(1, $products->calls);
        self::assertCount(2, $cart->items);
        self::assertSame('Perfume', $cart->items[0]->title);
        self::assertSame(2, $cart->items[0]->quantity);
        self::assertSame(20000, $cart->items[0]->totalMinorUnits());
        self::assertSame(40000, $cart->items[0]->oldTotalMinorUnits());
        self::assertNull($cart->items[1]->oldTotalMinorUnits());
        self::assertSame(5, $cart->items[0]->availableUnits);
        self::assertTrue($cart->items[0]->canIncreaseQuantity());
        self::assertSame(50, $cart->items[0]->discountPercent());
        self::assertNull($cart->items[1]->discountPercent());

        self::assertCount(1, $cart->totals);
        self::assertSame('BYN', $cart->totals[0]->currencyCode);
        self::assertSame(40550, $cart->totals[0]->productsMinorUnits);
        self::assertSame(20000, $cart->totals[0]->discountMinorUnits);
        self::assertSame(20550, $cart->totals[0]->totalMinorUnits);
    }

    public function testCurrenciesAreNeverMixed(): void
    {
        $this->addToCart([10 => 1, 11 => 1]);
        $products = new FakeCartProductProvider([
            10 => new CartProductDTO(10, 'A', 'B', null, 100, null, 'BYN', 5),
            11 => new CartProductDTO(11, 'C', 'D', null, 200, null, 'USD', 5),
        ]);

        $totals = $this->handler($products)()->totals;

        self::assertSame(['BYN', 'USD'], array_map(static fn ($total): string => $total->currencyCode, $totals));
        self::assertSame([100, 200], array_map(static fn ($total): int => $total->totalMinorUnits, $totals));
    }

    public function testProductsThatAreNoLongerActiveAreSkipped(): void
    {
        $this->addToCart([10 => 1, 11 => 1]);
        $products = new FakeCartProductProvider([
            11 => new CartProductDTO(11, 'Cream', 'Dior', null, 550, null, 'BYN', 5),
        ]);

        $cart = $this->handler($products)();

        self::assertCount(1, $cart->items);
        self::assertSame(11, $cart->items[0]->productId);
        self::assertSame(550, $cart->totals[0]->totalMinorUnits);
    }

    public function testReadsOnlyTheCurrentOwnersCart(): void
    {
        $this->addToCart([10 => 1]);
        $products = new FakeCartProductProvider([10 => new CartProductDTO(10, 'A', 'B', null, 100, null, 'BYN', 5)]);

        $this->owners->current = CartOwner::guest(hash('sha256', 'someone else'));

        self::assertTrue($this->handler($products)()->isEmpty());
    }

    /** @param array<int, int> $quantitiesByProductId */
    private function addToCart(array $quantitiesByProductId): void
    {
        $now = new \DateTime();
        $cart = Cart::create($this->owner, $now);
        foreach ($quantitiesByProductId as $productId => $quantity) {
            $cart->addProduct($productId, Quantity::of($quantity), StockLimit::ofUnits(50), $now);
        }
        $this->carts->save($cart);
    }

    private function handler(FakeCartProductProvider $products): GetCartHandler
    {
        return new GetCartHandler($this->carts, $this->owners, $products);
    }
}
