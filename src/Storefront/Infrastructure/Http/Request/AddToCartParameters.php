<?php

declare(strict_types=1);

namespace App\Storefront\Infrastructure\Http\Request;

use App\Cart\Application\FrontUseCases\Input\AddToCartDTO;
use App\Cart\Domain\Entity\Cart;
use Symfony\Component\Validator\Constraints as Assert;

/** HTTP shape of the add-to-cart JSON payload. Only shape, type and range checks live here (-> 422). */
final readonly class AddToCartParameters
{
    public function __construct(
        #[Assert\Positive]
        public int $productId,
        #[Assert\Range(min: 1, max: Cart::MAX_QUANTITY_PER_PRODUCT)]
        public int $quantity = 1,
    ) {
    }

    public function toDto(): AddToCartDTO
    {
        return new AddToCartDTO($this->productId, $this->quantity);
    }
}
