<?php

namespace App\Catalog\Application\Characteristic\AdminUseCases\Exception;

class CharAlreadyInUseException extends \RuntimeException
{
    public static function withTitle(string $title, ?\Throwable $previous = null)
    {
        return new self(sprintf('A characteristic with title "%s" already exists.', $title), previous: $previous);
    }
}
