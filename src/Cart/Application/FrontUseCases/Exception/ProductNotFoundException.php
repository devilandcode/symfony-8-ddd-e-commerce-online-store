<?php

declare(strict_types=1);

namespace App\Cart\Application\FrontUseCases\Exception;

final class ProductNotFoundException extends \RuntimeException
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Product with ID %d was not found.', $id));
    }
}
