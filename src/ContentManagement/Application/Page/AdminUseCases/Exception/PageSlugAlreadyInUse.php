<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\Page\AdminUseCases\Exception;

final class PageSlugAlreadyInUse extends \RuntimeException
{
    public static function withSlug(string $slug, ?\Throwable $previous = null): self
    {
        return new self(sprintf('A page with slug "%s" already exists.', $slug), previous: $previous);
    }
}
