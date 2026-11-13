<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\Page\AdminUseCases\Output;

final readonly class PageContentEditorDTO
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public string $pageTitle,
        public string $pageSlug,
        public array $payload,
    ) {
    }
}
