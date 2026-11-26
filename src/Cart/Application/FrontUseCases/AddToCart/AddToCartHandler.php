<?php

declare(strict_types=1);

namespace App\Cart\Application\FrontUseCases\AddToCart;

use App\Cart\Application\FrontUseCases\Exception\ProductNotFoundException;
use App\Cart\Application\FrontUseCases\Exception\ProductNotPurchasableException;
use App\Cart\Application\FrontUseCases\Input\AddToCartDTO;
use App\Cart\Application\FrontUseCases\Output\CartSummaryDTO;
use App\Cart\Application\Interface\CartOwnerProviderInterface;
use App\Cart\Application\Interface\PurchasableProductProviderInterface;
use App\Cart\Domain\Entity\Cart;
use App\Cart\Domain\Exception\InvalidCartQuantityException;
use App\Cart\Domain\Repository\CartRepositoryInterface;
use App\Shared\Application\FlusherInterface;

final readonly class AddToCartHandler
{
    public function __construct(
        private CartRepositoryInterface $cartRepository,
        private CartOwnerProviderInterface $ownerProvider,
        private PurchasableProductProviderInterface $productProvider,
        private FlusherInterface $flusher,
    ) {
    }

    /**
     * @throws ProductNotFoundException
     * @throws ProductNotPurchasableException
     * @throws InvalidCartQuantityException
     */
    public function __invoke(AddToCartDTO $data): CartSummaryDTO
    {
        $product = $this->productProvider->find($data->productId)
            ?? throw ProductNotFoundException::withId($data->productId);

        if (!$product->canBePurchased()) {
            throw ProductNotPurchasableException::withId($data->productId);
        }

        $owner = $this->ownerProvider->resolveCurrent();
        $now = new \DateTime();
        $cart = $this->cartRepository->findByOwner($owner) ?? Cart::create($owner, $now);

        $cart->addProduct($product->id, $data->quantity, $product->availableUnits, $now);

        $this->cartRepository->save($cart);
        $this->flusher->commit();

        return new CartSummaryDTO($cart->itemsCount(), $cart->getProductIds());
    }
}
