<?php

declare(strict_types=1);

namespace App\Tests\ContentManagement\Application;

use App\ContentManagement\Application\Page\AdminUseCases\SavePageContent\SavePageContentHandler;
use App\ContentManagement\Domain\Entity\Page;
use App\ContentManagement\Domain\ValueObject\PageContentData;
use App\Shared\Application\Flusher;
use App\Shared\Domain\ValueObject\Slug;
use App\Tests\ContentManagement\Double\InMemoryPageContentRepository;
use App\Tests\ContentManagement\Double\InMemoryPageRepository;
use PHPUnit\Framework\TestCase;

final class SavePageContentHandlerTest extends TestCase
{
    public function testSaveCreatesAndUpdatesOneContentRowPerPage(): void
    {
        $pages = new InMemoryPageRepository();
        $contents = new InMemoryPageContentRepository();
        $flusher = new RecordingFlusher();
        $pages->pages[7] = $this->page(7, 'home');
        $handler = new SavePageContentHandler($pages, $contents, $flusher);

        $created = PageContentData::fromArray([
            'slides' => [
                ['heading' => 'Summer', 'tags' => ['new', 'sale']],
                ['heading' => 'Winter', 'tags' => []],
            ],
            'sections' => [
                'new_arrivals' => ['heading' => 'New arrivals'],
            ],
        ]);
        $handler(7, $created);

        $updated = PageContentData::fromArray([
            'slides' => [
                ['heading' => 'Autumn', 'tags' => ['sale']],
            ],
        ]);
        $handler(7, $updated);

        self::assertSame(1, $contents->count());
        self::assertSame(2, $flusher->commits);
        self::assertSame($updated->toArray(), $contents->findByPageId(7)?->payload()->toArray());
    }

    private function page(int $id, string $slug): Page
    {
        $page = Page::create($slug, new Slug($slug), null, null, null, null, null, 0, true, new \DateTime());
        $property = new \ReflectionProperty(Page::class, 'id');
        $property->setValue($page, $id);

        return $page;
    }
}

final class RecordingFlusher implements Flusher
{
    public int $commits = 0;

    public function commit(): void
    {
        ++$this->commits;
    }
}
