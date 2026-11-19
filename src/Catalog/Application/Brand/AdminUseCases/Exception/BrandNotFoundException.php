<?php

declare(strict_types=1);

namespace App\Catalog\Application\Brand\AdminUseCases\Exception;

final class BrandNotFoundException extends \RuntimeException
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Brand with ID %d was not found.', $id));
    }
}
