<?php

declare(strict_types=1);

namespace App\Catalog\Application\Brand\AdminUseCases\Exception;

final class BrandHasProductsException extends \RuntimeException
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Brand with ID %d cannot be deleted while products use it.', $id));
    }
}
