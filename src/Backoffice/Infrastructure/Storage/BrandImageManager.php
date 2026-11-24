<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Storage;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class BrandImageManager
{
    public function __construct(private BrandImageUploader $uploader)
    {
    }

    public function prepare(?string $currentImage, bool $removeImage, ?UploadedFile $imageUpload): BrandImageChanges
    {
        $uploadedPath = null;
        $image = $removeImage ? null : $currentImage;

        if (null !== $imageUpload) {
            $image = $this->uploader->upload($imageUpload);
            $uploadedPath = $image;
        }

        return new BrandImageChanges(
            image: $image,
            uploadedPath: $uploadedPath,
            obsoletePath: $currentImage === $image ? null : $currentImage,
        );
    }

    public function discardUpload(BrandImageChanges $changes): void
    {
        $this->uploader->remove($changes->uploadedPath);
    }

    public function removeObsolete(BrandImageChanges $changes): void
    {
        $this->uploader->remove($changes->obsoletePath);
    }

    public function removeImage(?string $image): void
    {
        $this->uploader->remove($image);
    }
}
