<?php

declare(strict_types=1);

namespace App\Catalog\Application\Category\AdminUseCases\Exception;

class CategoryNotFoundException extends \RuntimeException
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Category with ID "%d" was not found.', $id));
    }
}
