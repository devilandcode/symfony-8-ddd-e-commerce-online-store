<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\Page\Exception;

final class PageNotFound extends \RuntimeException
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Page with ID "%d" was not found.', $id));
    }
}
