<?php

declare(strict_types=1);

namespace App\Catalog\Application\Characteristic\AdminUseCases\Exception;

final class CharacteristicNotFoundException extends \RuntimeException
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Characteristic with ID %d was not found.', $id));
    }
}
