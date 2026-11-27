<?php

declare(strict_types=1);

namespace App\Storefront\Infrastructure\Http\Request;

use App\Cart\Application\FrontUseCases\Input\RemoveFromCartDTO;
use Symfony\Component\Validator\Constraints as Assert;

/** HTTP shape of the remove-from-cart JSON payload. */
final readonly class RemoveFromCartParameters
{
    public function __construct(
        #[Assert\Positive]
        public int $productId,
    ) {
    }

    public function toDto(): RemoveFromCartDTO
    {
        return new RemoveFromCartDTO($this->productId);
    }
}
