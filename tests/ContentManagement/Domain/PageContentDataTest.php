<?php

declare(strict_types=1);

namespace App\Tests\ContentManagement\Domain;

use App\ContentManagement\Domain\ValueObject\PageContentData;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class PageContentDataTest extends TestCase
{
    public function testNestedListsRoundTrip(): void
    {
        $payload = [
            'slides' => [
                [
                    'heading' => 'Summer offers',
                    'desktopImage' => 'uploads/page-content/0123456789abcdef0123456789abcdef.webp',
                    'isActive' => true,
                    'tags' => ['new', 'sale'],
                ],
                [
                    'heading' => 'Winter',
                    'desktopImage' => null,
                    'isActive' => false,
                    'tags' => [],
                ],
            ],
            'sections' => [
                'new_arrivals' => ['heading' => 'New arrivals', 'buttonLabel' => 'Browse', 'buttonUrl' => '/catalog'],
            ],
        ];

        $content = PageContentData::fromArray($payload);

        self::assertSame($payload, $content->toArray());
        self::assertSame($payload, PageContentData::fromArray($content->toArray())->toArray());
    }

    public function testEmptyPayloadIsValid(): void
    {
        self::assertSame([], PageContentData::empty()->toArray());
    }

    public function testRootKeysMustBeStrings(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        PageContentData::fromArray([['heading' => 'Nested list']]);
    }

    public function testEmptyKeysAreRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        PageContentData::fromArray(['' => 'value']);
    }

    public function testObjectsIncludingUploadedFilesAreRejected(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'page-content');
        self::assertNotFalse($file);
        file_put_contents($file, 'image');

        try {
            $this->expectException(\InvalidArgumentException::class);

            PageContentData::fromArray([
                'desktopImage' => new UploadedFile($file, 'slide.png', 'image/png', null, true),
            ]);
        } finally {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    public function testNonFiniteNumbersAreRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        PageContentData::fromArray(['ratio' => \NAN]);
    }
}
