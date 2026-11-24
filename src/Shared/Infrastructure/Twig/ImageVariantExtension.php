<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Twig;

use App\Shared\Infrastructure\Image\ImageVariantProcessor;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ImageVariantExtension extends AbstractExtension
{
    public function __construct(private readonly ImageVariantProcessor $imageVariantProcessor)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('image_variant', $this->createVariant(...)),
        ];
    }

    public function createVariant(?string $sourcePath, int $width, int $height, string $fit = 'contain'): ?string
    {
        return $this->imageVariantProcessor->create($sourcePath, $width, $height, $fit);
    }
}
