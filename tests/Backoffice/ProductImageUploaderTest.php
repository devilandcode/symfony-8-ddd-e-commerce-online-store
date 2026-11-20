<?php

declare(strict_types=1);

namespace App\Tests\Backoffice;

use App\Backoffice\Infrastructure\Storage\ProductImageUploader;
use App\Backoffice\Infrastructure\Storage\ProductImageSetManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class ProductImageUploaderTest extends TestCase
{
    private string $projectDirectory;

    protected function setUp(): void
    {
        $this->projectDirectory = sys_get_temp_dir().'/product-images-'.bin2hex(random_bytes(4));
        mkdir($this->projectDirectory.'/public', 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->projectDirectory);
    }

    public function testUploadStoresImageUnderProductsAndCanRemoveIt(): void
    {
        $file = $this->uploadedPng('product.png');
        $uploader = new ProductImageUploader($this->projectDirectory);

        $path = $uploader->upload($file);

        self::assertMatchesRegularExpression('~^uploads/products/[a-f0-9]{32}\.png$~', $path);
        self::assertTrue($uploader->isStoredPath($path));
        self::assertFileExists($this->projectDirectory.'/public/'.$path);

        $uploader->remove($path);

        self::assertFileDoesNotExist($this->projectDirectory.'/public/'.$path);
    }

    public function testRemovalSelectionsAreAppliedAndFilesAreRemovedAfterPersistence(): void
    {
        $image = 'uploads/products/aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.png';
        $selectedAdditionalImage = 'uploads/products/bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb.png';
        $keptAdditionalImage = 'uploads/products/cccccccccccccccccccccccccccccccc.jpg';
        $this->createStoredImage($image);
        $this->createStoredImage($selectedAdditionalImage);
        $this->createStoredImage($keptAdditionalImage);

        $manager = new ProductImageSetManager(new ProductImageUploader($this->projectDirectory));
        $changes = $manager->prepare(
            $image,
            [$selectedAdditionalImage, $keptAdditionalImage],
            true,
            [$selectedAdditionalImage],
            null,
            [],
        );

        self::assertNull($changes->image);
        self::assertSame([$keptAdditionalImage], $changes->additionalImages);
        self::assertEqualsCanonicalizing([$image, $selectedAdditionalImage], $changes->obsoletePaths);
        self::assertFileExists($this->projectDirectory.'/public/'.$image);
        self::assertFileExists($this->projectDirectory.'/public/'.$selectedAdditionalImage);

        $manager->removeObsolete($changes);

        self::assertFileDoesNotExist($this->projectDirectory.'/public/'.$image);
        self::assertFileDoesNotExist($this->projectDirectory.'/public/'.$selectedAdditionalImage);
        self::assertFileExists($this->projectDirectory.'/public/'.$keptAdditionalImage);
    }

    public function testRemovalSelectionsDoNotDeleteUnrelatedImages(): void
    {
        $image = 'uploads/products/aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.png';
        $additionalImage = 'uploads/products/bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb.png';
        $this->createStoredImage($image);
        $this->createStoredImage($additionalImage);

        $manager = new ProductImageSetManager(new ProductImageUploader($this->projectDirectory));
        $changes = $manager->prepare($image, [$additionalImage], false, [], null, []);
        $manager->removeObsolete($changes);

        self::assertSame($image, $changes->image);
        self::assertSame([$additionalImage], $changes->additionalImages);
        self::assertSame([], $changes->obsoletePaths);
        self::assertFileExists($this->projectDirectory.'/public/'.$image);
        self::assertFileExists($this->projectDirectory.'/public/'.$additionalImage);
    }

    private function uploadedPng(string $name): UploadedFile
    {
        $path = $this->projectDirectory.'/'.$name;
        file_put_contents($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', true));

        return new UploadedFile($path, $name, 'image/png', null, true);
    }

    private function createStoredImage(string $path): void
    {
        $absolutePath = $this->projectDirectory.'/public/'.$path;
        mkdir(dirname($absolutePath), 0777, true);
        file_put_contents($absolutePath, 'image');
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
