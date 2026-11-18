<?php

declare(strict_types=1);

namespace App\Catalog\Application\Characteristic\AdminUseCases\Input;

final readonly class CharDataDTO
{
    public function __construct(
        public string $title,
        public ?string $measure,
        public int $position,
        public bool $isActive,
    ){        
    }
}
