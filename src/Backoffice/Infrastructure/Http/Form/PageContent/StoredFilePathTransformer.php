<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Form\PageContent;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Keeps an already stored public path when the admin does not upload a replacement.
 *
 * @implements DataTransformerInterface<string|null, null>
 */
final class StoredFilePathTransformer implements DataTransformerInterface
{
    private ?string $storedPath = null;

    public function transform(mixed $value): mixed
    {
        $this->storedPath = \is_string($value) && '' !== trim($value) ? $value : null;

        return null;
    }

    public function reverseTransform(mixed $value): mixed
    {
        if ($value instanceof UploadedFile) {
            return $value;
        }

        return $this->storedPath;
    }
}
