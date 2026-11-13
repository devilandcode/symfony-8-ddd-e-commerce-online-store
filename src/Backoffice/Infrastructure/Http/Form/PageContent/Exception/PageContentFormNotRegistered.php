<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Form\PageContent\Exception;

final class PageContentFormNotRegistered extends \RuntimeException
{
    public static function forSlug(string $slug): self
    {
        return new self(sprintf('No page content form is registered for the page slug "%s".', $slug));
    }
}
