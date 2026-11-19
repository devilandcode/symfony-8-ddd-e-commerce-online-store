<?php

declare(strict_types=1);

namespace App\Catalog\Application\Category\AdminUseCases\Exception;

final class CategoryHasProductsException extends \RuntimeException
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Category with ID %d cannot be deleted while products use it.', $id));
    }
}
