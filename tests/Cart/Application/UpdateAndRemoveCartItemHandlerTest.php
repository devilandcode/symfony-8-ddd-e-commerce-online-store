<?php

declare(strict_types=1);

namespace App\Tests\Cart\Application;

use App\Cart\Application\FrontUseCases\AddToCart\AddToCartHandler;
use App\Cart\Application\FrontUseCases\Exception\ProductNotFoundException;
use App\Cart\Application\FrontUseCases\Exception\ProductNotPurchasableException;
use App\Cart\Application\FrontUseCases\GetCart\GetCartHandler;
use App\Cart\Application\FrontUseCases\Input\AddToCartDTO;
use App\Cart\Application\FrontUseCases\Input\RemoveFromCartDTO;
use App\Cart\Application\FrontUseCases\Input\UpdateCartItemQuantityDTO;
use App\Cart\Application\FrontUseCases\Output\CartProductDTO;
use App\Cart\Application\FrontUseCases\Output\PurchasableProductDTO;
use App\Cart\Application\FrontUseCases\RemoveFromCart\RemoveFromCartHandler;
use App\Cart\Application\FrontUseCases\UpdateCartItemQuantity\UpdateCartItemQuantityHandler;
use App\Cart\Domain\Exception\InvalidCartQuantityException;
use App\Cart\Domain\Exception\ProductNotInCartException;
use App\Cart\Domain\ValueObject\CartOwner;
use App\Cart\Domain\ValueObject\Quantity;
use App\Cart\Domain\ValueObject\StockLimit;
use App\Tests\Cart\Double\CountingFlusher;
use App\Tests\Cart\Double\FakeCartProductProvider;
use App\Tests\Cart\Double\FakeOwnerProvider;
use App\Tests\Cart\Double\FakeProductProvider;
use App\Tests\Cart\Double\InMemoryCartRepository;
use PHPUnit\Framework\TestCase;

final class UpdateAndRemoveCartItemHandlerTest extends TestCase
{
    private InMemoryCartRepository $carts;
    private FakeOwnerProvider $owners;
    private CountingFlusher $flusher;
    private AddToCartHandler $add;
    private UpdateCartItemQuantityHandler $update;
    private RemoveFromCartHandler $remove;

    protected function setUp(): void
    {
        $this->carts = new InMemoryCartRepository();
        $this->owners = new FakeOwnerProvider(CartOwner::guest(hash('sha256', 'guest')));
        $purchasable = new FakeProductProvider([
            1 => new PurchasableProductDTO(1, true, 10),
            2 => new PurchasableProductDTO(2, true, 3),
            3 => new PurchasableProductDTO(3, false, 10),
        ]);
        $display = new FakeCartProductProvider([
            1 => new CartProductDTO(1, 'Perfume', 'Burberry', null, 1000, 2000, 'BYN', 10),
            2 => new CartProductDTO(2, 'Cream', 'Dior', null, 500, null, 'BYN', 3),
        ]);
        $this->flusher = new CountingFlusher();
        $getCart = new GetCartHandler($this->carts, $this->owners, $display);

        $this->add = new AddToCartHandler($this->carts, $this->owners, $purchasable, $this->flusher);
        $this->update = new UpdateCartItemQuantityHandler($this->carts, $this->owners, $purchasable, $getCart, $this->flusher);
        $this->remove = new RemoveFromCartHandler($this->carts, $this->owners, $getCart, $this->flusher);

        ($this->add)(new AddToCartDTO(1, 2));
        ($this->add)(new AddToCartDTO(2, 1));
        $this->flusher->commits = 0;
    }

    public function testUpdateSetsQuantityAndReturnsTheRefreshedCart(): void
    {
        $result = ($this->update)(new UpdateCartItemQuantityDTO(1, 7));

        self::assertSame(8, $result->summary->totalQuantity);
        self::assertSame(7, $result->cart->items[0]->quantity);
        self::assertSame(7000, $result->cart->items[0]->totalMinorUnits());
        self::assertSame(7000 + 500, $result->cart->totals[0]->totalMinorUnits);
        self::assertSame(1, $this->flusher->commits);
    }

    public function testQuantityUpToTheStockBalanceIsAcceptedAndAboveIsRejected(): void
    {
        ($this->update)(new UpdateCartItemQuantityDTO(1, 10));

        $this->expectException(InvalidCartQuantityException::class);

        ($this->update)(new UpdateCartItemQuantityDTO(1, 11));
    }

    public function testRejectedUpdateIsNotPersisted(): void
    {
        try {
            ($this->update)(new UpdateCartItemQuantityDTO(2, 4));
            self::fail('Expected exception.');
        } catch (InvalidCartQuantityException) {
        }

        self::assertSame(0, $this->flusher->commits);
        self::assertSame(1, $this->carts->all()[0]->getItems()[1]->getQuantity()->getValue());
    }

    public function testUpdateOfAProductNotInTheCartIsRejected(): void
    {
        $this->expectException(ProductNotInCartException::class);

        ($this->update)(new UpdateCartItemQuantityDTO(3, 1));
    }

    public function testUpdateWithoutAnyCartIsRejected(): void
    {
        $this->owners->current = CartOwner::guest(hash('sha256', 'someone else'));

        $this->expectException(ProductNotInCartException::class);

        ($this->update)(new UpdateCartItemQuantityDTO(1, 1));
    }

    public function testUpdateOfAProductRemovedFromCatalogOrDeactivatedIsRejected(): void
    {
        $this->carts->all()[0]->addProduct(3, Quantity::of(1), StockLimit::ofUnits(10), new \DateTime());
        $this->carts->all()[0]->addProduct(4, Quantity::of(1), StockLimit::ofUnits(10), new \DateTime());

        foreach ([[3, ProductNotPurchasableException::class], [4, ProductNotFoundException::class]] as [$productId, $exception]) {
            try {
                ($this->update)(new UpdateCartItemQuantityDTO($productId, 1));
                self::fail('Expected exception.');
            } catch (\Throwable $thrown) {
                self::assertInstanceOf($exception, $thrown);
            }
        }
    }

    public function testRemoveDropsTheLineAndReturnsTheRefreshedCart(): void
    {
        $result = ($this->remove)(new RemoveFromCartDTO(1));

        self::assertSame(1, $result->summary->totalQuantity);
        self::assertCount(1, $result->cart->items);
        self::assertSame(2, $result->cart->items[0]->productId);
        self::assertSame(1, $this->flusher->commits);
    }

    public function testRemovingTheLastProductEmptiesTheCart(): void
    {
        ($this->remove)(new RemoveFromCartDTO(1));
        $result = ($this->remove)(new RemoveFromCartDTO(2));

        self::assertSame(0, $result->summary->totalQuantity);
        self::assertTrue($result->cart->isEmpty());
    }

    public function testRemoveOfAProductNotInTheCartIsRejected(): void
    {
        try {
            ($this->remove)(new RemoveFromCartDTO(3));
            self::fail('Expected exception.');
        } catch (ProductNotInCartException) {
        }

        self::assertSame(0, $this->flusher->commits);
    }

    public function testRemoveWithoutOwnerIsRejected(): void
    {
        $this->owners->current = null;

        $this->expectException(ProductNotInCartException::class);

        ($this->remove)(new RemoveFromCartDTO(1));
    }
}
