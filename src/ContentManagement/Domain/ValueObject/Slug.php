<?php

namespace App\ContentManagement\Domain\ValueObject;

class Slug
{
    private string $slug;

    public function __construct(string $slug, object $entity)
    {
        $this->assertSlugIsValid($slug, $entity);
        $this->slug = $slug;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    private function assertSlugIsValid(string $slug, object $entity): void
    {
        if (empty($slug)) {
            throw new \InvalidArgumentException('Slug cannot be empty.');
        }

        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            throw new \InvalidArgumentException('Slug must contain only lowercase letters, numbers, and hyphens.');
        }

        if (strlen($slug) > 255) {
            throw new \InvalidArgumentException('Slug cannot exceed 255 characters.');
        }
    }
}
