<?php

declare(strict_types=1);

namespace App\Cart\Application\FrontUseCases\UpdateCartItemQuantity;

use App\Cart\Application\FrontUseCases\Exception\ProductNotFoundException;
use App\Cart\Application\FrontUseCases\Exception\ProductNotPurchasableException;
use App\Cart\Application\FrontUseCases\GetCart\GetCartHandler;
use App\Cart\Application\FrontUseCases\Input\UpdateCartItemQuantityDTO;
use App\Cart\Application\FrontUseCases\Output\CartChangeResultDTO;
use App\Cart\Application\FrontUseCases\Output\CartSummaryDTO;
use App\Cart\Application\Interface\CartOwnerProviderInterface;
use App\Cart\Application\Interface\PurchasableProductProviderInterface;
use App\Cart\Domain\Exception\InvalidCartQuantityException;
use App\Cart\Domain\Exception\ProductNotInCartException;
use App\Cart\Domain\Repository\CartRepositoryInterface;
use App\Cart\Domain\ValueObject\Quantity;
use App\Cart\Domain\ValueObject\StockLimit;
use App\Shared\Application\FlusherInterface;

final readonly class UpdateCartItemQuantityHandler
{
    public function __construct(
        private CartRepositoryInterface $cartRepository,
        private CartOwnerProviderInterface $ownerProvider,
        private PurchasableProductProviderInterface $productProvider,
        private GetCartHandler $getCart,
        private FlusherInterface $flusher,
    ) {
    }

    /**
     * @throws ProductNotInCartException
     * @throws ProductNotFoundException
     * @throws ProductNotPurchasableException
     * @throws InvalidCartQuantityException
     */
    public function __invoke(UpdateCartItemQuantityDTO $data): CartChangeResultDTO
    {
        $owner = $this->ownerProvider->findCurrent();
        $cart = null === $owner ? null : $this->cartRepository->findByOwner($owner);

        if (null === $cart || !$cart->containsProduct($data->productId)) {
            throw ProductNotInCartException::withProductId($data->productId);
        }

        $product = $this->productProvider->find($data->productId)
            ?? throw ProductNotFoundException::withId($data->productId);

        if (!$product->canBePurchased()) {
            throw ProductNotPurchasableException::withId($data->productId);
        }

        $cart->changeQuantity(
            $product->id,
            Quantity::of($data->quantity),
            StockLimit::ofUnits($product->availableUnits),
            new \DateTime(),
        );

        $this->cartRepository->save($cart);
        $this->flusher->commit();

        return new CartChangeResultDTO(
            new CartSummaryDTO($cart->getTotalQuantity(), $cart->getProductIds()),
            ($this->getCart)(),
        );
    }
}
