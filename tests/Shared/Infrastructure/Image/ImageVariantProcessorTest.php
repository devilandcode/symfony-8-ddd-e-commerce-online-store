<?php

declare(strict_types=1);

namespace App\Tests\Shared\Infrastructure\Image;

use App\Shared\Infrastructure\Image\ImageVariantProcessor;
use Imagick;
use ImagickPixel;
use PHPUnit\Framework\TestCase;

final class ImageVariantProcessorTest extends TestCase
{
    private string $projectDirectory;
    private string $sourcePath;

    protected function setUp(): void
    {
        $this->projectDirectory = sys_get_temp_dir().'/image-variants-'.bin2hex(random_bytes(5));
        $this->sourcePath = 'uploads/products/test-source.png';
        mkdir(dirname($this->projectDirectory.'/public/'.$this->sourcePath), 0777, true);

        $source = new Imagick();
        $source->newImage(200, 100, new ImagickPixel('red'), 'PNG');
        $source->writeImage($this->projectDirectory.'/public/'.$this->sourcePath);
        $source->clear();
        $source->destroy();
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->projectDirectory);
    }

    public function testContainCreatesAnExactSizeVariantAndPreservesTheSourceFormat(): void
    {
        $sourceFile = $this->projectDirectory.'/public/'.$this->sourcePath;
        $sourceHash = hash_file('sha256', $sourceFile);
        $processor = new ImageVariantProcessor($this->projectDirectory);

        $variantPath = $processor->create($this->sourcePath, 100, 100, 'contain');
        self::assertNotNull($variantPath);
        $variantFile = $this->projectDirectory.'/public/'.$variantPath;
        self::assertFileExists($variantFile);
        self::assertNotSame(0, fileperms($variantFile) & 0444);
        self::assertSame($sourceHash, hash_file('sha256', $sourceFile));

        $variant = new Imagick($variantFile);
        self::assertSame(100, $variant->getImageWidth());
        self::assertSame(100, $variant->getImageHeight());
        self::assertSame('PNG', $variant->getImageFormat());
        $centerColor = $variant->getImagePixelColor(50, 50)->getColor();
        self::assertSame(255, $centerColor['r']);
        self::assertSame(0, $centerColor['g']);
        self::assertSame(0, $centerColor['b']);
        self::assertGreaterThan(0.9, $variant->getImagePixelColor(0, 0)->getColorValue(Imagick::COLOR_ALPHA));
        $variant->clear();
        $variant->destroy();
    }

    public function testCoverCreatesAnExactSizeVariantAndReusesCachedOutput(): void
    {
        $processor = new ImageVariantProcessor($this->projectDirectory);

        $variantPath = $processor->create($this->sourcePath, 100, 100, 'cover');
        self::assertNotNull($variantPath);
        $variantFile = $this->projectDirectory.'/public/'.$variantPath;
        $firstModifiedAt = filemtime($variantFile);

        self::assertSame($variantPath, $processor->create($this->sourcePath, 100, 100, 'cover'));
        self::assertSame($firstModifiedAt, filemtime($variantFile));
        self::assertNotSame($variantPath, $processor->create($this->sourcePath, 100, 100, 'contain'));

        $variant = new Imagick($variantFile);
        self::assertSame(100, $variant->getImageWidth());
        self::assertSame(100, $variant->getImageHeight());
        $variant->clear();
        $variant->destroy();
    }

    public function testJpegAndWebpVariantsKeepTheirSourceFormats(): void
    {
        $processor = new ImageVariantProcessor($this->projectDirectory);

        foreach (['JPEG' => 'jpg', 'WEBP' => 'webp'] as $format => $extension) {
            $relativeSourcePath = sprintf('uploads/products/format-source.%s', $extension);
            $absoluteSourcePath = $this->projectDirectory.'/public/'.$relativeSourcePath;
            $image = new Imagick();
            $image->newImage(80, 40, new ImagickPixel('green'), $format);
            $image->writeImage($absoluteSourcePath);
            $image->clear();
            $image->destroy();

            $variantPath = $processor->create($relativeSourcePath, 40, 20, 'contain');
            self::assertNotNull($variantPath);
            self::assertStringEndsWith('.'.$extension, $variantPath);

            $variant = new Imagick($this->projectDirectory.'/public/'.$variantPath);
            self::assertSame($format, $variant->getImageFormat());
            $variant->clear();
            $variant->destroy();
        }
    }

    public function testItRejectsUnsafePathsDimensionsAndFitModes(): void
    {
        $processor = new ImageVariantProcessor($this->projectDirectory);

        try {
            $processor->create('../secret.png', 100, 100);
            self::fail('Unsafe source path should be rejected.');
        } catch (\InvalidArgumentException) {
            self::assertTrue(true);
        }

        try {
            $processor->create($this->sourcePath, 0, 100);
            self::fail('Invalid dimensions should be rejected.');
        } catch (\InvalidArgumentException) {
            self::assertTrue(true);
        }

        try {
            $processor->create($this->sourcePath, 100, 100, 'stretch');
            self::fail('Unsupported fit modes should be rejected.');
        } catch (\InvalidArgumentException) {
            self::assertTrue(true);
        }
    }

    public function testOptionalAndMissingSourcesHaveExplicitResults(): void
    {
        $processor = new ImageVariantProcessor($this->projectDirectory);

        self::assertNull($processor->create(null, 100, 100));

        $this->expectException(\RuntimeException::class);
        $processor->create('uploads/products/missing.png', 100, 100);
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
