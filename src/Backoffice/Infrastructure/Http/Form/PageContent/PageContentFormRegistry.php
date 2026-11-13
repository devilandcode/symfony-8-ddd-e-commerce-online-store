<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Form\PageContent;

use App\Backoffice\Infrastructure\Http\Form\PageContent\Exception\PageContentFormNotRegistered;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class PageContentFormRegistry
{
    /** @var array<string, class-string> */
    private array $typesBySlug;

    /**
     * @param iterable<mixed, object> $formTypes
     */
    public function __construct(
        #[AutowireIterator('app.page_content_form')]
        iterable $formTypes,
    ) {
        $types = [];
        foreach ($formTypes as $formType) {
            $reflection = new \ReflectionClass($formType);
            $slug = self::requireSlug($reflection);
            if (isset($types[$slug])) {
                throw new \LogicException(sprintf('Multiple page content forms are registered for slug "%s".', $slug));
            }

            $types[$slug] = $reflection->getName();
        }

        $this->typesBySlug = $types;
    }

    public function has(string $slug): bool
    {
        return isset($this->typesBySlug[$slug]);
    }

    /**
     * @return class-string
     */
    public function typeFor(string $slug): string
    {
        return $this->typesBySlug[$slug] ?? throw PageContentFormNotRegistered::forSlug($slug);
    }

    /**
     * @param \ReflectionClass<object> $reflection
     */
    private static function requireSlug(\ReflectionClass $reflection): string
    {
        $attributes = $reflection->getAttributes(PageContentForm::class);
        if ([] === $attributes) {
            throw new \LogicException(sprintf('Page content form "%s" is missing the #[PageContentForm] attribute.', $reflection->getName()));
        }

        if (\count($attributes) > 1) {
            throw new \LogicException(sprintf('Page content form "%s" must declare exactly one #[PageContentForm] attribute.', $reflection->getName()));
        }

        $registeredSlug = $attributes[0]->getArguments()['slug'] ?? $attributes[0]->getArguments()[0] ?? null;
        if (!\is_string($registeredSlug) || '' === $registeredSlug) {
            throw new \LogicException(sprintf('Page content form "%s" must declare a slug.', $reflection->getName()));
        }

        return $registeredSlug;
    }
}
