<?php

declare(strict_types=1);

namespace App\Tests\Backoffice;

use App\Backoffice\Infrastructure\Storage\PageContentImageUploader;
use App\Backoffice\Infrastructure\Storage\PageContentPayloadNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class PageContentPayloadNormalizerTest extends TestCase
{
    private string $projectDirectory;

    protected function setUp(): void
    {
        $this->projectDirectory = sys_get_temp_dir().'/page-content-'.bin2hex(random_bytes(4));
        mkdir($this->projectDirectory.'/public', 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->projectDirectory);
    }

    public function testUploadedFilesAreReplacedByAStablePublicPath(): void
    {
        $normalizer = $this->normalizer();

        $first = $normalizer->normalize('home', [
            'slides' => [
                ['desktopImage' => $this->uploadedPng('first.png')],
            ],
        ], []);
        $second = $normalizer->normalize('home', [
            'slides' => [
                ['desktopImage' => $this->uploadedPng('second.png')],
            ],
        ], $first);

        $path = $first['slides'][0]['desktopImage'];
        self::assertIsString($path);
        self::assertSame($path, $second['slides'][0]['desktopImage']);
        self::assertMatchesRegularExpression('~^uploads/page-content/[a-f0-9]{32}\.png$~', $path);
        self::assertFileExists($this->projectDirectory.'/public/'.$path);
    }

    public function testAMissingUploadKeepsThePreviousPath(): void
    {
        $normalizer = $this->normalizer();
        $previous = [
            'slides' => [
                [
                    'heading' => 'Summer',
                    'desktopImage' => 'uploads/page-content/0123456789abcdef0123456789abcdef.png',
                    'tags' => ['new', 'sale'],
                ],
            ],
        ];

        $normalized = $normalizer->normalize('home', [
            'slides' => [
                [
                    'heading' => ' Summer ',
                    'desktopImage' => null,
                    'tags' => ['new', 'sale'],
                ],
            ],
        ], $previous);

        self::assertSame('Summer', $normalized['slides'][0]['heading']);
        self::assertSame($previous['slides'][0]['desktopImage'], $normalized['slides'][0]['desktopImage']);
        self::assertSame(['new', 'sale'], $normalized['slides'][0]['tags']);
    }

    private function normalizer(): PageContentPayloadNormalizer
    {
        return new PageContentPayloadNormalizer(new PageContentImageUploader($this->projectDirectory));
    }

    private function uploadedPng(string $name): UploadedFile
    {
        $path = $this->projectDirectory.'/'.$name;
        file_put_contents($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', true));

        return new UploadedFile($path, $name, 'image/png', null, true);
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
