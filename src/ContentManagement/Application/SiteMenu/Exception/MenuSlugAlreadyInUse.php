<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\SiteMenu\Exception;

final class MenuSlugAlreadyInUse extends \RuntimeException
{
    public static function withSlug(string $slug, ?\Throwable $previous = null): self
    {
        return new self(sprintf('A sitemenu with slug "%s" already exists.', $slug), previous: $previous);
    }
}
