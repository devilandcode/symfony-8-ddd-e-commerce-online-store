<?php

declare(strict_types=1);

namespace App\Cart\Application\Interface;

use App\Cart\Application\FrontUseCases\Output\CartProductDTO;
use App\Cart\Domain\Entity\Cart;

/** Anti-corruption port: read-only Catalog data used to display cart lines. */
interface CartProductProviderInterface
{
    /**
     * Returns the active products of the cart lines; products that were deactivated or removed are silently absent.
     * Must use a single query for the whole cart.
     *
     * @return array<int, CartProductDTO> indexed by product id
     */
    public function findActiveInCart(Cart $cart): array;
}
