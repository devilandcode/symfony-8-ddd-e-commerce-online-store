<?php

declare(strict_types=1);

namespace App\Customer\Application\AdminUseCases\Exception;

final class UserNotFound extends \RuntimeException
{
    public static function withId(int $id): self
    {
        return new self(sprintf('User with ID %d was not found.', $id));
    }
}
