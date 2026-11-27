<?php

declare(strict_types=1);

namespace App\Storefront\Infrastructure\Http\Request;

use App\Cart\Application\FrontUseCases\Input\UpdateCartItemQuantityDTO;
use Symfony\Component\Validator\Constraints as Assert;

/** HTTP shape of the cart quantity update JSON payload. Only positive integers are checked here (-> 422). */
final readonly class UpdateCartItemParameters
{
    public function __construct(
        #[Assert\Positive]
        public int $productId,
        #[Assert\Positive]
        public int $quantity,
    ) {
    }

    public function toDto(): UpdateCartItemQuantityDTO
    {
        return new UpdateCartItemQuantityDTO($this->productId, $this->quantity);
    }
}
