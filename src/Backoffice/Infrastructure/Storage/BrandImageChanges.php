<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Storage;

final readonly class BrandImageChanges
{
    public function __construct(
        public ?string $image,
        public ?string $uploadedPath,
        public ?string $obsoletePath,
    ) {
    }
}
