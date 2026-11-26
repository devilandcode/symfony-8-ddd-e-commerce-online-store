<?php

declare(strict_types=1);

namespace App\Cart\Application\FrontUseCases\GetCart;

use App\Cart\Application\FrontUseCases\Output\CartDTO;
use App\Cart\Application\FrontUseCases\Output\CartItemDTO;
use App\Cart\Application\FrontUseCases\Output\CartTotalDTO;
use App\Cart\Application\Interface\CartOwnerProviderInterface;
use App\Cart\Application\Interface\CartProductProviderInterface;
use App\Cart\Domain\Repository\CartRepositoryInterface;

final readonly class GetCartHandler
{
    public function __construct(
        private CartRepositoryInterface $cartRepository,
        private CartOwnerProviderInterface $ownerProvider,
        private CartProductProviderInterface $productProvider,
    ) {
    }

    public function __invoke(): CartDTO
    {
        $owner = $this->ownerProvider->findCurrent();
        $cart = null === $owner ? null : $this->cartRepository->findByOwner($owner);

        if (null === $cart || 0 === $cart->itemsCount()) {
            return CartDTO::empty();
        }

        $products = $this->productProvider->findActiveByIds($cart->getProductIds());

        $items = [];
        foreach ($cart->getItems() as $cartItem) {
            $product = $products[$cartItem->getProductId()] ?? null;
            if (null === $product) {
                // The product was deactivated or removed from the catalog after it was added to the cart.
                continue;
            }

            $items[] = new CartItemDTO(
                productId: $product->id,
                title: $product->title,
                brandTitle: $product->brandTitle,
                image: $product->image,
                quantity: $cartItem->getQuantity(),
                unitPriceMinorUnits: $product->priceMinorUnits,
                unitOldPriceMinorUnits: $product->oldPriceMinorUnits,
                currencyCode: $product->currencyCode,
            );
        }

        return new CartDTO($items, $this->calculateTotals($items));
    }

    /**
     * @param list<CartItemDTO> $items
     *
     * @return list<CartTotalDTO>
     */
    private function calculateTotals(array $items): array
    {
        /** @var array<string, array{products: int, total: int}> $sums */
        $sums = [];
        foreach ($items as $item) {
            $sums[$item->currencyCode] ??= ['products' => 0, 'total' => 0];
            $sums[$item->currencyCode]['products'] += $item->oldTotalMinorUnits() ?? $item->totalMinorUnits();
            $sums[$item->currencyCode]['total'] += $item->totalMinorUnits();
        }

        $totals = [];
        foreach ($sums as $currencyCode => $sum) {
            $totals[] = new CartTotalDTO(
                currencyCode: (string) $currencyCode,
                productsMinorUnits: $sum['products'],
                discountMinorUnits: $sum['products'] - $sum['total'],
                totalMinorUnits: $sum['total'],
            );
        }

        return $totals;
    }
}
