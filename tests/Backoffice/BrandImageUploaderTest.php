<?php

declare(strict_types=1);

namespace App\Tests\Backoffice;

use App\Backoffice\Infrastructure\Storage\BrandImageManager;
use App\Backoffice\Infrastructure\Storage\BrandImageUploader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class BrandImageUploaderTest extends TestCase
{
    private string $projectDirectory;

    protected function setUp(): void
    {
        $this->projectDirectory = sys_get_temp_dir().'/brand-images-'.bin2hex(random_bytes(4));
        mkdir($this->projectDirectory.'/public', 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->projectDirectory);
    }

    public function testUploadStoresAnImageUnderBrandsAndCanRemoveIt(): void
    {
        $uploader = new BrandImageUploader($this->projectDirectory);
        $path = $uploader->upload($this->uploadedPng('brand.png'));

        self::assertMatchesRegularExpression('~^uploads/brands/[a-f0-9]{32}\.png$~', $path);
        self::assertTrue($uploader->isStoredPath($path));
        self::assertFileExists($this->projectDirectory.'/public/'.$path);

        $uploader->remove($path);

        self::assertFileDoesNotExist($this->projectDirectory.'/public/'.$path);
    }

    public function testImageChangesPreserveReplaceAndRemoveStoredImages(): void
    {
        $currentImage = 'uploads/brands/aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.png';
        $this->createStoredImage($currentImage);
        $uploader = new BrandImageUploader($this->projectDirectory);
        $manager = new BrandImageManager($uploader);

        $unchanged = $manager->prepare($currentImage, false, null);
        self::assertSame($currentImage, $unchanged->image);
        self::assertNull($unchanged->obsoletePath);

        $removed = $manager->prepare($currentImage, true, null);
        self::assertNull($removed->image);
        self::assertSame($currentImage, $removed->obsoletePath);
        self::assertFileExists($this->projectDirectory.'/public/'.$currentImage);
        $manager->removeObsolete($removed);
        self::assertFileDoesNotExist($this->projectDirectory.'/public/'.$currentImage);

        $this->createStoredImage($currentImage);
        $replacement = $manager->prepare($currentImage, false, $this->uploadedPng('replacement.png'));
        self::assertNotSame($currentImage, $replacement->image);
        self::assertSame($currentImage, $replacement->obsoletePath);
        self::assertFileExists($this->projectDirectory.'/public/'.$replacement->image);
        $manager->discardUpload($replacement);
        self::assertFileDoesNotExist($this->projectDirectory.'/public/'.$replacement->image);
        self::assertFileExists($this->projectDirectory.'/public/'.$currentImage);
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
