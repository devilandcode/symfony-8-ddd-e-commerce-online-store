<?php

namespace App\ContentManagement\Application\Page\FrontUseCases\Exception;

final class PageNotFound extends \RuntimeException
{
    public static function withSlug(string $slug): self
    {
        return new self(sprintf('Page with slug "%s" was not found.', $slug));
    }
}
