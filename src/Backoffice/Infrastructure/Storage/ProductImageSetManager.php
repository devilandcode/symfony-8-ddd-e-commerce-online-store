<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Storage;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class ProductImageSetManager
{
    public function __construct(private ProductImageUploader $uploader)
    {
    }

    /**
     * @param list<string>|null $currentAdditionalImages
     * @param list<UploadedFile> $additionalImageUploads
     * @param list<string> $additionalImagesToRemove
     */
    public function prepare(
        ?string $currentImage,
        ?array $currentAdditionalImages,
        bool $removeImage,
        array $additionalImagesToRemove,
        ?UploadedFile $imageUpload,
        array $additionalImageUploads,
    ): ProductImageChanges {
        $uploadedPaths = [];

        try {
            $image = $removeImage ? null : $currentImage;
            if (null !== $imageUpload) {
                $image = $this->uploader->upload($imageUpload);
                $uploadedPaths[] = $image;
            }

            $additionalImages = array_values(array_diff(
                $currentAdditionalImages ?? [],
                $additionalImagesToRemove,
            ));
            foreach ($additionalImageUploads as $file) {
                $path = $this->uploader->upload($file);
                $uploadedPaths[] = $path;
                $additionalImages[] = $path;
            }

            $oldPaths = array_values(array_unique(array_filter(
                array_merge([$currentImage], $currentAdditionalImages ?? []),
                static fn (?string $path): bool => null !== $path,
            )));
            $newPaths = array_values(array_unique(array_filter(
                array_merge([$image], $additionalImages),
                static fn (?string $path): bool => null !== $path,
            )));

            return new ProductImageChanges(
                $image,
                $additionalImages,
                $uploadedPaths,
                array_values(array_diff($oldPaths, $newPaths)),
            );
        } catch (\Throwable $exception) {
            $this->discardUploads($uploadedPaths);

            throw $exception;
        }
    }

    public function discardUploads(ProductImageChanges $changes): void
    {
        foreach ($changes->uploadedPaths as $path) {
            $this->uploader->remove($path);
        }
    }

    public function removeObsolete(ProductImageChanges $changes): void
    {
        foreach ($changes->obsoletePaths as $path) {
            $this->uploader->remove($path);
        }
    }
}
