<?php

declare(strict_types=1);

namespace App\Storefront\Infrastructure\Http\Request;

use App\Cart\Application\FrontUseCases\Input\AddToCartDTO;
use Symfony\Component\Validator\Constraints as Assert;

/** HTTP shape of the add-to-cart JSON payload. Only shape and positive-integer checks (the stock limit is a domain rule) live here (-> 422). */
final readonly class AddToCartParameters
{
    public function __construct(
        #[Assert\Positive]
        public int $productId,
        #[Assert\Positive]
        public int $quantity = 1,
    ) {
    }

    public function toDto(): AddToCartDTO
    {
        return new AddToCartDTO($this->productId, $this->quantity);
    }
}
