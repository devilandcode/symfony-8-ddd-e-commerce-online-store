<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\SiteMenu\AdminUseCases\Exception;

class MenuNotFound extends \RuntimeException
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Menu with ID "%d" was not found.', $id));
    }
}
