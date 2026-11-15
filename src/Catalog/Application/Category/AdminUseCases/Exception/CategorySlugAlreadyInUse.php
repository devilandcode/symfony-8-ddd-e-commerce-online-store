<?php

namespace App\Catalog\Application\Category\AdminUseCases\Exception;

final class CategorySlugAlreadyInUse extends \RuntimeException
{
    public static function withSlug(string $slug, ?\Throwable $previous = null): self
    {
        return new self(sprintf('A category with slug "%s" already exists.', $slug), previous: $previous);
    }
}

