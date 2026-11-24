<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Image;

use Imagick;
use ImagickPixel;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class ImageVariantProcessor
{
    private const MAX_DIMENSION = 3000;
    private const MAX_SOURCE_PIXELS = 50000000;
    private const SUPPORTED_FORMATS = ['JPEG', 'PNG', 'WEBP'];

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private string $projectDirectory,
    ) {
    }

    public function create(?string $sourcePath, int $width, int $height, string $fit = 'contain'): ?string
    {
        if (null === $sourcePath || '' === $sourcePath) {
            return null;
        }

        $this->validateDimensions($width, $height);
        if (!\in_array($fit, ['contain', 'cover'], true)) {
            throw new \InvalidArgumentException('Image fit must be either "contain" or "cover".');
        }

        $absoluteSourcePath = $this->resolveSourcePath($sourcePath);
        $sourceSize = filesize($absoluteSourcePath);
        $sourceModifiedAt = filemtime($absoluteSourcePath);
        $sourceChangedAt = filectime($absoluteSourcePath);
        $sourceInode = fileinode($absoluteSourcePath);
        if (false === $sourceSize || false === $sourceModifiedAt || false === $sourceChangedAt || false === $sourceInode) {
            throw new \RuntimeException(sprintf('Unable to inspect image source "%s".', $sourcePath));
        }

        $probe = new Imagick();
        try {
            $probe->pingImage($absoluteSourcePath.'[0]');
            $probe->setIteratorIndex(0);
            $sourceWidth = $probe->getImageWidth();
            $sourceHeight = $probe->getImageHeight();
            $format = strtoupper($probe->getImageFormat());
        } catch (\ImagickException $exception) {
            throw new \RuntimeException(sprintf('Unable to inspect image source "%s".', $sourcePath), previous: $exception);
        } finally {
            $probe->clear();
            $probe->destroy();
        }

        if (!\in_array($format, self::SUPPORTED_FORMATS, true)) {
            throw new \RuntimeException(sprintf('Image source "%s" uses an unsupported format.', $sourcePath));
        }
        if ($sourceWidth <= 0 || $sourceHeight <= 0 || $sourceWidth * $sourceHeight > self::MAX_SOURCE_PIXELS) {
            throw new \RuntimeException(sprintf('Image source "%s" has invalid or excessive dimensions.', $sourcePath));
        }

        $cacheKey = hash('sha256', implode("\0", [
            $sourcePath,
            (string) $sourceModifiedAt,
            (string) $sourceChangedAt,
            (string) $sourceInode,
            (string) $sourceSize,
            (string) $sourceWidth,
            (string) $sourceHeight,
            $format,
            (string) $width,
            (string) $height,
            $fit,
        ]));
        $relativeVariantPath = sprintf('uploads/variants/%s/%s.%s', substr($cacheKey, 0, 2), $cacheKey, $this->extensionFor($format));
        $absoluteVariantPath = $this->projectDirectory.'/public/'.$relativeVariantPath;
        if (is_file($absoluteVariantPath)) {
            return $relativeVariantPath;
        }

        $directory = dirname($absoluteVariantPath);
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new \RuntimeException(sprintf('Unable to create image variant directory "%s".', $directory));
        }

        $this->writeVariant($absoluteSourcePath, $absoluteVariantPath, $width, $height, $fit, $format);

        return $relativeVariantPath;
    }

    private function validateDimensions(int $width, int $height): void
    {
        if ($width < 1 || $height < 1 || $width > self::MAX_DIMENSION || $height > self::MAX_DIMENSION) {
            throw new \InvalidArgumentException(sprintf(
                'Image variant dimensions must be between 1 and %d pixels.',
                self::MAX_DIMENSION,
            ));
        }
    }

    private function resolveSourcePath(string $sourcePath): string
    {
        if (
            !preg_match('~^uploads/(?:products|brands|page-content)/[A-Za-z0-9._/-]+$~D', $sourcePath)
            || str_contains($sourcePath, '//')
        ) {
            throw new \InvalidArgumentException('Image variants can only be created from uploaded image paths.');
        }

        foreach (explode('/', $sourcePath) as $segment) {
            if ('.' === $segment || '..' === $segment) {
                throw new \InvalidArgumentException('Image source paths cannot traverse directories.');
            }
        }

        $uploadsDirectory = realpath($this->projectDirectory.'/public/uploads');
        $absoluteSourcePath = realpath($this->projectDirectory.'/public/'.$sourcePath);
        if (
            false === $uploadsDirectory
            || false === $absoluteSourcePath
            || !is_file($absoluteSourcePath)
            || !str_starts_with($absoluteSourcePath, $uploadsDirectory.DIRECTORY_SEPARATOR)
        ) {
            throw new \RuntimeException(sprintf('Uploaded image source "%s" was not found.', $sourcePath));
        }

        return $absoluteSourcePath;
    }

    private function writeVariant(
        string $sourcePath,
        string $variantPath,
        int $width,
        int $height,
        string $fit,
        string $format,
    ): void {
        $temporaryPath = tempnam(dirname($variantPath), 'image-variant-');
        if (false === $temporaryPath) {
            throw new \RuntimeException('Unable to create a temporary image variant file.');
        }

        $image = new Imagick();
        try {
            $image->readImage($sourcePath.'[0]');
            $image->setIteratorIndex(0);
            if (method_exists($image, 'autoOrient')) {
                $image->autoOrient();
            } elseif (method_exists($image, 'autoOrientate')) {
                $image->autoOrientate();
            } elseif (method_exists($image, 'autoOrientImage')) {
                $image->autoOrientImage();
            } else {
                throw new \RuntimeException('The Imagick extension does not provide image orientation support.');
            }

            if ('cover' === $fit) {
                $image->cropThumbnailImage($width, $height);
            } else {
                $image->thumbnailImage($width, $height, true, true);
                $canvas = new Imagick();
                $background = 'JPEG' === $format ? new ImagickPixel('white') : new ImagickPixel('transparent');
                $canvas->newImage($width, $height, $background, $format);
                if ('JPEG' !== $format) {
                    $canvas->setImageAlphaChannel(Imagick::ALPHACHANNEL_ACTIVATE);
                }
                $canvas->setImageGravity(Imagick::GRAVITY_CENTER);
                $canvas->compositeImage($image, Imagick::COMPOSITE_OVER, 0, 0);
                $image->clear();
                $image->destroy();
                $image = $canvas;
            }

            $image->setImageFormat($format);
            if (\in_array($format, ['JPEG', 'WEBP'], true)) {
                $image->setImageCompressionQuality(85);
            }

            if (
                !$image->writeImage($temporaryPath)
                || !chmod($temporaryPath, 0644)
                || !rename($temporaryPath, $variantPath)
            ) {
                throw new \RuntimeException(sprintf('Unable to write image variant "%s".', $variantPath));
            }
        } catch (\ImagickException $exception) {
            throw new \RuntimeException(sprintf('Unable to process image source "%s".', $sourcePath), previous: $exception);
        } finally {
            $image->clear();
            $image->destroy();
            if (is_file($temporaryPath)) {
                unlink($temporaryPath);
            }
        }
    }

    private function extensionFor(string $format): string
    {
        return match ($format) {
            'JPEG' => 'jpg',
            'PNG' => 'png',
            'WEBP' => 'webp',
        };
    }
}
