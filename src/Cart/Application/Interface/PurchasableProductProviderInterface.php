<?php

declare(strict_types=1);

namespace App\Cart\Application\Interface;

use App\Cart\Application\FrontUseCases\Output\PurchasableProductDTO;

/** Anti-corruption port: what the cart needs to know to decide whether a product can be added. */
interface PurchasableProductProviderInterface
{
    public function find(int $productId): ?PurchasableProductDTO;
}
