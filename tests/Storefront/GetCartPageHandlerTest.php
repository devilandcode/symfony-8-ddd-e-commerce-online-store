<?php

declare(strict_types=1);

namespace App\Tests\Storefront;

use App\Cart\Application\FrontUseCases\GetCart\GetCartHandler;
use App\ContentManagement\Application\Page\FrontUseCases\Exception\PageNotFoundException;
use App\ContentManagement\Application\Page\FrontUseCases\FindBySlug\FindPageBySlugHandler;
use App\ContentManagement\Domain\Entity\Page;
use App\ContentManagement\Domain\Repository\PageContentRepositoryInterface;
use App\ContentManagement\Domain\Repository\PageRepositoryInterface;
use App\Shared\Domain\ValueObject\Slug;
use App\Storefront\Application\CartPage\Handler\GetCartPageHandler;
use App\Tests\Cart\Double\FakeCartProductProvider;
use App\Tests\Cart\Double\FakeOwnerProvider;
use App\Tests\Cart\Double\InMemoryCartRepository;
use PHPUnit\Framework\TestCase;

final class GetCartPageHandlerTest extends TestCase
{
    public function testItCombinesTheCartPageAndTheCart(): void
    {
        $page = Page::create('Cart', new Slug('cart'), null, null, null, null, null, 0, true, new \DateTime('2026-01-01 00:00:00'));
        (new \ReflectionProperty(Page::class, 'id'))->setValue($page, 3);

        $pageRepository = $this->createMock(PageRepositoryInterface::class);
        $pageRepository->expects(self::once())
            ->method('findBySlug')
            ->with(self::callback(static fn (Slug $slug): bool => 'cart' === $slug->toString()))
            ->willReturn($page);
        $pageContentRepository = $this->createMock(PageContentRepositoryInterface::class);
        $pageContentRepository->method('findByPageId')->with(3)->willReturn(null);

        $cartPage = $this->handler($pageRepository, $pageContentRepository)();

        self::assertSame('Cart', $cartPage->page->title);
        self::assertTrue($cartPage->cart->isEmpty());
    }

    public function testMissingCartPageIsReportedAsPageNotFound(): void
    {
        $pageRepository = $this->createMock(PageRepositoryInterface::class);
        $pageRepository->method('findBySlug')->willReturn(null);

        $this->expectException(PageNotFoundException::class);

        ($this->handler($pageRepository, $this->createMock(PageContentRepositoryInterface::class)))();
    }

    private function handler(PageRepositoryInterface $pages, PageContentRepositoryInterface $contents): GetCartPageHandler
    {
        return new GetCartPageHandler(
            new FindPageBySlugHandler($pages, $contents),
            new GetCartHandler(new InMemoryCartRepository(), new FakeOwnerProvider(null), new FakeCartProductProvider([])),
        );
    }
}
