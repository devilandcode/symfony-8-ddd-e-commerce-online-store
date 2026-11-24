<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Storage;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class BrandImageUploader
{
    private const PATH_PATTERN = '~^uploads/brands/[a-f0-9]{32}\.(?:jpg|png|webp)$~';

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private string $projectDirectory,
    ) {
    }

    public function upload(UploadedFile $file): string
    {
        $extension = $file->guessExtension();
        if ('jpeg' === $extension) {
            $extension = 'jpg';
        }

        if (!\in_array($extension, ['jpg', 'png', 'webp'], true)) {
            throw new \RuntimeException('The uploaded brand image has an unsupported file type.');
        }

        $directory = $this->projectDirectory.'/public/uploads/brands';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new \RuntimeException('Unable to create the brand image upload directory.');
        }

        $filename = bin2hex(random_bytes(16)).'.'.$extension;
        $file->move($directory, $filename);

        return 'uploads/brands/'.$filename;
    }

    public function remove(?string $path): void
    {
        if (null === $path || !$this->isStoredPath($path)) {
            return;
        }

        $absolutePath = $this->projectDirectory.'/public/'.$path;
        if (is_file($absolutePath) && !unlink($absolutePath)) {
            throw new \RuntimeException(sprintf('Unable to remove the brand image "%s".', $path));
        }
    }

    public function isStoredPath(string $path): bool
    {
        return 1 === preg_match(self::PATH_PATTERN, $path);
    }
}
