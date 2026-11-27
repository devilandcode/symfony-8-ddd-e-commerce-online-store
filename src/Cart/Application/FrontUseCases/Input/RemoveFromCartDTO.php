<?php

declare(strict_types=1);

namespace App\Cart\Application\FrontUseCases\Input;

final readonly class RemoveFromCartDTO
{
    public function __construct(public int $productId)
    {
    }
}
