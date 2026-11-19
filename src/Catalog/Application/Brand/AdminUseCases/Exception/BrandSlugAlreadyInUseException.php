<?php

declare(strict_types=1);

namespace App\Catalog\Application\Brand\AdminUseCases\Exception;

final class BrandSlugAlreadyInUseException extends \RuntimeException
{
    public static function withSlug(string $slug, ?\Throwable $previous = null): self
    {
        return new self(sprintf('A brand with slug "%s" already exists.', $slug), previous: $previous);
    }
}
