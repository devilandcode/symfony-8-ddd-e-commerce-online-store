<?php

declare(strict_types=1);

namespace App\Tests\ContentManagement\Application;

use App\ContentManagement\Application\SiteMenu\FrontUseCases\ListActiveRootMenus\ListActiveRootMenusHandler;
use App\ContentManagement\Domain\Entity\SiteMenu;
use App\ContentManagement\Domain\Repository\SiteMenuRepositoryInterface;
use App\Shared\Domain\ValueObject\Slug;
use PHPUnit\Framework\TestCase;

final class ListActiveRootMenusHandlerTest extends TestCase
{
    public function testItReturnsTheActiveRootMenusFromTheRepository(): void
    {
        $menu = SiteMenu::create(
            'Perfume',
            null,
            true,
            new Slug('perfume'),
            1,
            true,
            new \DateTime(),
        );

        $repository = $this->createMock(SiteMenuRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findActiveRoots')
            ->willReturn([$menu]);

        $result = (new ListActiveRootMenusHandler($repository))();

        self::assertSame([$menu], $result);
    }
}
