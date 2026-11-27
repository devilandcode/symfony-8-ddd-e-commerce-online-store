<?php

declare(strict_types=1);

namespace App\Cart\Domain\Exception;

final class ProductNotInCartException extends \DomainException
{
    public static function withProductId(int $productId): self
    {
        return new self(sprintf('Product with ID %d is not in the cart.', $productId));
    }
}
