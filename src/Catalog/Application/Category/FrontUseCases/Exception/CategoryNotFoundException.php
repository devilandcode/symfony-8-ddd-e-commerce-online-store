<?php

namespace App\Catalog\Application\Category\FrontUseCases\Exception;

class CategoryNotFoundException extends \RuntimeException
{
    public static function withSlug(string $slug): self
    {
        return new self(sprintf('Category with slug "%s" was not found.', $slug));
    }
}
