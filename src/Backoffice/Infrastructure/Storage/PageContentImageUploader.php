<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Storage;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class PageContentImageUploader
{
    private const PATH_PATTERN = '~^uploads/page-content/[a-f0-9]{32}\.(?:jpg|png|webp)$~';

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private string $projectDirectory,
    ) {
    }

    public function upload(UploadedFile $file, string $pageSlug, string $fieldPath): string
    {
        $extension = $file->guessExtension();
        if ('jpeg' === $extension) {
            $extension = 'jpg';
        }

        if (!\in_array($extension, ['jpg', 'png', 'webp'], true)) {
            throw new \RuntimeException('The uploaded image has an unsupported file type.');
        }

        if ('' === $pageSlug || '' === $fieldPath) {
            throw new \InvalidArgumentException('A page slug and field path are required to store an image.');
        }

        $directory = $this->projectDirectory.'/public/uploads/page-content';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new \RuntimeException('Unable to create the page content upload directory.');
        }

        $filename = substr(hash('sha256', $pageSlug."\0".$fieldPath), 0, 32).'.'.$extension;
        $file->move($directory, $filename);

        return 'uploads/page-content/'.$filename;
    }

    public function remove(?string $path): void
    {
        if (null === $path || !$this->isStoredPath($path)) {
            return;
        }

        $absolutePath = $this->projectDirectory.'/public/'.$path;
        if (is_file($absolutePath) && !unlink($absolutePath)) {
            throw new \RuntimeException(sprintf('Unable to remove the previous page content image "%s".', $path));
        }
    }

    public function isStoredPath(string $path): bool
    {
        return 1 === preg_match(self::PATH_PATTERN, $path);
    }
}
