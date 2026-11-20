<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Storage;

final readonly class ProductImageChanges
{
    /**
     * @param list<string> $additionalImages
     * @param list<string> $uploadedPaths
     * @param list<string> $obsoletePaths
     */
    public function __construct(
        public ?string $image,
        public array $additionalImages,
        public array $uploadedPaths,
        public array $obsoletePaths,
    ) {
    }
}
