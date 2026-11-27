<?php

declare(strict_types=1);

namespace App\Storefront\Infrastructure\Http\Presenter;

use App\Cart\Application\FrontUseCases\Output\CartChangeResultDTO;
use App\Cart\Application\FrontUseCases\Output\CartItemDTO;
use App\Cart\Application\FrontUseCases\Output\CartTotalDTO;
use App\Storefront\Infrastructure\Money\PriceFormatter;

/** Builds the JSON contract the cart page script uses to refresh itself; money is formatted here, once. */
final readonly class CartChangePresenter
{
    public function __construct(private PriceFormatter $priceFormatter)
    {
    }

    /** @return array<string, mixed> */
    public function presentUpdate(CartChangeResultDTO $result, int $productId): array
    {
        $item = null;
        foreach ($result->cart->items as $candidate) {
            if ($candidate->productId === $productId) {
                $item = $candidate;
                break;
            }
        }

        return [...$this->presentCart($result), 'item' => null === $item ? null : $this->presentItem($item)];
    }

    /** @return array<string, mixed> */
    public function presentRemoval(CartChangeResultDTO $result, int $productId): array
    {
        return [...$this->presentCart($result), 'removedProductId' => $productId];
    }

    /** @return array<string, mixed> */
    private function presentCart(CartChangeResultDTO $result): array
    {
        return [
            'success' => true,
            'cartCount' => $result->summary->totalQuantity,
            'isEmpty' => $result->cart->isEmpty(),
            'totals' => array_map($this->presentTotal(...), $result->cart->totals),
        ];
    }

    /** @return array<string, mixed> */
    private function presentItem(CartItemDTO $item): array
    {
        $oldTotal = $item->oldTotalMinorUnits();

        return [
            'productId' => $item->productId,
            'quantity' => $item->quantity,
            'availableUnits' => $item->availableUnits,
            'canIncrease' => $item->canIncreaseQuantity(),
            'total' => $this->priceFormatter->format($item->totalMinorUnits(), $item->currencyCode),
            'oldTotal' => null === $oldTotal ? null : $this->priceFormatter->format($oldTotal, $item->currencyCode),
        ];
    }

    /** @return array<string, string> */
    private function presentTotal(CartTotalDTO $total): array
    {
        return [
            'currencyCode' => $total->currencyCode,
            'products' => $this->priceFormatter->format($total->productsMinorUnits, $total->currencyCode),
            'discount' => $this->priceFormatter->format($total->discountMinorUnits, $total->currencyCode),
            'total' => $this->priceFormatter->format($total->totalMinorUnits, $total->currencyCode),
        ];
    }
}
