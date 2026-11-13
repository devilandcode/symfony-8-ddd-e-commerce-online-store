<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Storage;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Replaces uploaded files with public paths before the payload reaches the domain.
 */
final readonly class PageContentPayloadNormalizer
{
    public function __construct(private PageContentImageUploader $uploader)
    {
    }

    /**
     * @param array<mixed, mixed> $submitted
     * @param array<mixed, mixed> $previous
     *
     * @return array<mixed, mixed>
     */
    public function normalize(string $pageSlug, array $submitted, array $previous): array
    {
        $normalized = $this->walk($submitted, $previous, $pageSlug, '');
        if (!\is_array($normalized)) {
            throw new \InvalidArgumentException('Page content payload must be a map.');
        }

        return $normalized;
    }

    /**
     * Deletes stored images that the new payload no longer references.
     * Call this only after the new payload has been saved.
     *
     * @param array<mixed, mixed> $previous
     * @param array<mixed, mixed> $current
     */
    public function removeUnused(array $previous, array $current): void
    {
        $kept = $this->storedPaths($current);
        foreach ($this->storedPaths($previous) as $path) {
            if (!\in_array($path, $kept, true)) {
                $this->uploader->remove($path);
            }
        }
    }

    private function walk(mixed $submitted, mixed $previous, string $pageSlug, string $fieldPath): mixed
    {
        if ($submitted instanceof UploadedFile) {
            return $this->uploader->upload($submitted, $pageSlug, $fieldPath);
        }

        if (null === $submitted && \is_string($previous) && $this->uploader->isStoredPath($previous)) {
            return $previous;
        }

        if (\is_array($submitted)) {
            $previousValues = \is_array($previous) ? $previous : [];
            $normalized = [];
            foreach ($submitted as $key => $value) {
                if (!\is_string($key) && !\is_int($key)) {
                    throw new \InvalidArgumentException('Page content field names must be strings or list indexes.');
                }

                $childPath = '' === $fieldPath ? (string) $key : $fieldPath.'.'.$key;
                $normalized[$key] = $this->walk($value, $previousValues[$key] ?? null, $pageSlug, $childPath);
            }

            if ($this->isIntegerKeyed($normalized)) {
                $normalized = array_values($normalized);
            }

            return $normalized;
        }

        if (\is_object($submitted) || \is_resource($submitted)) {
            throw new \InvalidArgumentException('Page content only accepts JSON scalars, lists, and maps.');
        }

        if (\is_string($submitted)) {
            return trim($submitted);
        }

        return $submitted;
    }

    /**
     * @param array<mixed, mixed> $values
     */
    private function isIntegerKeyed(array $values): bool
    {
        foreach (array_keys($values) as $key) {
            if (!\is_int($key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<mixed, mixed> $values
     *
     * @return list<string>
     */
    private function storedPaths(array $values): array
    {
        $paths = [];
        foreach ($values as $value) {
            if (\is_string($value) && $this->uploader->isStoredPath($value)) {
                $paths[] = $value;
            } elseif (\is_array($value)) {
                array_push($paths, ...$this->storedPaths($value));
            }
        }

        return $paths;
    }
}
