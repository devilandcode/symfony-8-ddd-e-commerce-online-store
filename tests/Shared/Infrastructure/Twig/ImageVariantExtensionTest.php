<?php

declare(strict_types=1);

namespace App\Tests\Shared\Infrastructure\Twig;

use App\Shared\Infrastructure\Image\ImageVariantProcessor;
use App\Shared\Infrastructure\Twig\ImageVariantExtension;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class ImageVariantExtensionTest extends TestCase
{
    public function testTwigFunctionCreatesVariantsAndHandlesOptionalImages(): void
    {
        $projectDirectory = sys_get_temp_dir().'/twig-image-variants-'.bin2hex(random_bytes(5));
        $sourcePath = 'uploads/brands/test-source.png';
        $sourceFile = $projectDirectory.'/public/'.$sourcePath;
        mkdir(dirname($sourceFile), 0777, true);

        $source = new \Imagick();
        $source->newImage(80, 40, new \ImagickPixel('blue'), 'PNG');
        $source->writeImage($sourceFile);
        $source->clear();
        $source->destroy();

        try {
            $twig = new Environment(new ArrayLoader([
                'variant' => '{{ image_variant(path, 40, 20, "contain") }}',
                'optional' => '{{ image_variant(null, 40, 20, "contain") is null ? "empty" : "unexpected" }}',
            ]));
            $twig->addExtension(new ImageVariantExtension(new ImageVariantProcessor($projectDirectory)));

            $variantPath = $twig->render('variant', ['path' => $sourcePath]);
            self::assertStringStartsWith('uploads/variants/', $variantPath);
            self::assertFileExists($projectDirectory.'/public/'.$variantPath);
            self::assertSame('empty', $twig->render('optional'));
        } finally {
            $this->removeDirectory($projectDirectory);
        }
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = scandir($directory);
        if (false === $items) {
            return;
        }

        foreach ($items as $item) {
            if ('.' === $item || '..' === $item) {
                continue;
            }

            $path = $directory.'/'.$item;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($directory);
    }
}
