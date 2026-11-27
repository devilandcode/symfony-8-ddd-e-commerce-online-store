<?php

declare(strict_types=1);

namespace App\Tests\Cart\Double;

use App\Cart\Application\FrontUseCases\Output\CartProductDTO;
use App\Cart\Application\Interface\CartProductProviderInterface;
use App\Cart\Domain\Entity\Cart;

final class FakeCartProductProvider implements CartProductProviderInterface
{
    public int $calls = 0;

    /** @param array<int, CartProductDTO> $products */
    public function __construct(private readonly array $products)
    {
    }

    public function findActiveInCart(Cart $cart): array
    {
        ++$this->calls;

        return array_intersect_key($this->products, array_flip($cart->getProductIds()));
    }
}
