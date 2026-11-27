<?php

declare(strict_types=1);

namespace App\Cart\Application\FrontUseCases\RemoveFromCart;

use App\Cart\Application\FrontUseCases\GetCart\GetCartHandler;
use App\Cart\Application\FrontUseCases\Input\RemoveFromCartDTO;
use App\Cart\Application\FrontUseCases\Output\CartChangeResultDTO;
use App\Cart\Application\FrontUseCases\Output\CartSummaryDTO;
use App\Cart\Application\Interface\CartOwnerProviderInterface;
use App\Cart\Domain\Exception\ProductNotInCartException;
use App\Cart\Domain\Repository\CartRepositoryInterface;
use App\Shared\Application\FlusherInterface;

final readonly class RemoveFromCartHandler
{
    public function __construct(
        private CartRepositoryInterface $cartRepository,
        private CartOwnerProviderInterface $ownerProvider,
        private GetCartHandler $getCart,
        private FlusherInterface $flusher,
    ) {
    }

    /** @throws ProductNotInCartException */
    public function __invoke(RemoveFromCartDTO $data): CartChangeResultDTO
    {
        $owner = $this->ownerProvider->findCurrent();
        $cart = null === $owner ? null : $this->cartRepository->findByOwner($owner);

        if (null === $cart) {
            throw ProductNotInCartException::withProductId($data->productId);
        }

        $cart->removeProduct($data->productId, new \DateTime());

        $this->cartRepository->save($cart);
        $this->flusher->commit();

        return new CartChangeResultDTO(
            new CartSummaryDTO($cart->getTotalQuantity(), $cart->getProductIds()),
            ($this->getCart)(),
        );
    }
}
