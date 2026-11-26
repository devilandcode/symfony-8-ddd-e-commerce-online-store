<?php

declare(strict_types=1);

namespace App\Cart\Application\FrontUseCases\GetCartSummary;

use App\Cart\Application\FrontUseCases\Output\CartSummaryDTO;
use App\Cart\Application\Interface\CartOwnerProviderInterface;
use App\Cart\Domain\Repository\CartRepositoryInterface;

final readonly class GetCartSummaryHandler
{
    public function __construct(
        private CartRepositoryInterface $cartRepository,
        private CartOwnerProviderInterface $ownerProvider,
    ) {
    }

    public function __invoke(): CartSummaryDTO
    {
        $owner = $this->ownerProvider->findCurrent();
        $cart = null === $owner ? null : $this->cartRepository->findByOwner($owner);

        return null === $cart
            ? CartSummaryDTO::empty()
            : new CartSummaryDTO($cart->itemsCount(), $cart->getProductIds());
    }
}
