<?php

declare(strict_types=1);

namespace App\Cart\Application\Interface;

use App\Cart\Application\FrontUseCases\Output\CartProductDTO;

/** Anti-corruption port: read-only Catalog data used to display cart lines. */
interface CartProductProviderInterface
{
    /**
     * Returns only active products; unknown or inactive ids are silently absent. Must use a single query.
     *
     * @param list<int> $productIds
     *
     * @return array<int, CartProductDTO> indexed by product id
     */
    public function findActiveByIds(array $productIds): array;
}
