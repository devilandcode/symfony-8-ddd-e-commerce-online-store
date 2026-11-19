<?php

declare(strict_types=1);

namespace App\Catalog\Application\Product\AdminUseCases\Exception;

final class ProductSlugAlreadyInUseException extends \RuntimeException
{
    public static function withSlug(string $slug, ?\Throwable $previous = null): self
    {
        return new self(sprintf('A product with slug "%s" already exists.', $slug), previous: $previous);
    }
}
