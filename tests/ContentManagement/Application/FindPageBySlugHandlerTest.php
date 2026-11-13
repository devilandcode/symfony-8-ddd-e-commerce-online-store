<?php

declare(strict_types=1);

namespace App\Tests\ContentManagement\Application;

use App\ContentManagement\Application\Page\FrontUseCases\FindBySlug\FindPageBySlugHandler;
use App\ContentManagement\Domain\Entity\Page;
use App\ContentManagement\Domain\Entity\PageContent;
use App\ContentManagement\Domain\ValueObject\PageContentData;
use App\Shared\Domain\ValueObject\Slug;
use App\Tests\ContentManagement\Double\InMemoryPageContentRepository;
use App\Tests\ContentManagement\Double\InMemoryPageRepository;
use PHPUnit\Framework\TestCase;

final class FindPageBySlugHandlerTest extends TestCase
{
    public function testAPageWithoutContentReturnsAnEmptyBag(): void
    {
        $pages = new InMemoryPageRepository();
        $pages->pages[4] = $this->page(4, 'contacts', 'Contacts');
        $handler = new FindPageBySlugHandler($pages, new InMemoryPageContentRepository());

        $page = $handler(new Slug('contacts'));

        self::assertSame('Contacts', $page->title);
        self::assertSame('H1', $page->h1);
        self::assertSame('<p>Body</p>', $page->content);
        self::assertSame('SEO', $page->seo_title);
        self::assertSame([], $page->pageContent->toArray());
        self::assertCount(0, $page->pageContent);
    }

    public function testStoredNestedContentIsReturnedInTheBag(): void
    {
        $pages = new InMemoryPageRepository();
        $contents = new InMemoryPageContentRepository();
        $page = $this->page(4, 'home', 'Home');
        $pages->pages[4] = $page;
        $payload = PageContentData::fromArray([
            'slides' => [
                ['heading' => 'Summer', 'tags' => ['new']],
            ],
        ]);
        $contents->save(PageContent::create($page, $payload));
        $handler = new FindPageBySlugHandler($pages, $contents);

        $view = $handler(new Slug('home'));

        self::assertSame($payload->toArray(), $view->pageContent->toArray());
        self::assertSame('Summer', $view->pageContent['slides'][0]['heading']);
    }

    private function page(int $id, string $slug, string $title): Page
    {
        $page = Page::create(
            $title,
            new Slug($slug),
            'H1',
            'Description',
            '<p>Body</p>',
            'SEO',
            'SEO description',
            1,
            true,
            new \DateTime('2026-01-01 00:00:00'),
        );
        $property = new \ReflectionProperty(Page::class, 'id');
        $property->setValue($page, $id);

        return $page;
    }
}
