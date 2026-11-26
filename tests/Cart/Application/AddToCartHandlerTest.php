<?php

declare(strict_types=1);

namespace App\Tests\Cart\Application;

use App\Cart\Application\FrontUseCases\AddToCart\AddToCartHandler;
use App\Cart\Application\FrontUseCases\Exception\ProductNotFoundException;
use App\Cart\Application\FrontUseCases\Exception\ProductNotPurchasableException;
use App\Cart\Application\FrontUseCases\GetCartSummary\GetCartSummaryHandler;
use App\Cart\Application\FrontUseCases\Input\AddToCartDTO;
use App\Cart\Application\FrontUseCases\Output\PurchasableProductDTO;
use App\Cart\Domain\Exception\InvalidCartQuantityException;
use App\Cart\Domain\ValueObject\CartOwner;
use App\Tests\Cart\Double\CountingFlusher;
use App\Tests\Cart\Double\FakeOwnerProvider;
use App\Tests\Cart\Double\FakeProductProvider;
use App\Tests\Cart\Double\InMemoryCartRepository;
use PHPUnit\Framework\TestCase;

final class AddToCartHandlerTest extends TestCase
{
    private InMemoryCartRepository $carts;
    private FakeOwnerProvider $owners;
    private FakeProductProvider $products;
    private CountingFlusher $flusher;
    private AddToCartHandler $handler;

    protected function setUp(): void
    {
        $this->carts = new InMemoryCartRepository();
        $this->owners = new FakeOwnerProvider(CartOwner::guest(hash('sha256', 'guest')));
        $this->products = new FakeProductProvider([
            1 => new PurchasableProductDTO(1, true, 10),
            2 => new PurchasableProductDTO(2, false, 10),
            3 => new PurchasableProductDTO(3, true, 0),
        ]);
        $this->flusher = new CountingFlusher();
        $this->handler = new AddToCartHandler($this->carts, $this->owners, $this->products, $this->flusher);
    }

    public function testCreatesCartAndReturnsSummary(): void
    {
        $summary = ($this->handler)(new AddToCartDTO(1, 2));

        self::assertSame(1, $summary->itemsCount);
        self::assertTrue($summary->contains(1));
        self::assertCount(1, $this->carts->all());
        self::assertSame(1, $this->flusher->commits);
    }

    public function testAddingSameProductTwiceKeepsOneLineAndIncreasesQuantity(): void
    {
        ($this->handler)(new AddToCartDTO(1, 2));
        $summary = ($this->handler)(new AddToCartDTO(1, 3));

        self::assertSame(1, $summary->itemsCount);
        self::assertCount(1, $this->carts->all());
        self::assertSame(5, $this->carts->all()[0]->getItems()[0]->getQuantity());
    }

    public function testUnknownProductIsRejectedWithoutPersisting(): void
    {
        try {
            ($this->handler)(new AddToCartDTO(404));
            self::fail('Expected exception.');
        } catch (ProductNotFoundException) {
        }

        self::assertSame([], $this->carts->all());
        self::assertSame(0, $this->flusher->commits);
        self::assertSame(0, $this->owners->resolveCalls, 'No guest identity must be created for a rejected request.');
    }

    public function testInactiveOrOutOfStockProductsCannotBePurchased(): void
    {
        foreach ([2, 3] as $productId) {
            try {
                ($this->handler)(new AddToCartDTO($productId));
                self::fail('Expected exception.');
            } catch (ProductNotPurchasableException) {
            }
        }

        self::assertSame(0, $this->flusher->commits);
    }

    public function testQuantityAboveStockIsRejected(): void
    {
        $this->expectException(InvalidCartQuantityException::class);

        ($this->handler)(new AddToCartDTO(1, 11));
    }

    public function testSummaryHandlerReadsOnlyTheCurrentOwnersCart(): void
    {
        ($this->handler)(new AddToCartDTO(1));
        $summaryHandler = new GetCartSummaryHandler($this->carts, $this->owners);

        self::assertSame(1, $summaryHandler()->itemsCount);

        $this->owners->current = CartOwner::guest(hash('sha256', 'someone else'));
        self::assertSame(0, $summaryHandler()->itemsCount);

        $this->owners->current = null;
        self::assertSame(0, $summaryHandler()->itemsCount);
    }
}
