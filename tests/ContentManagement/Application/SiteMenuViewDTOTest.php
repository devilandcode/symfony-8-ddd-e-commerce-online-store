<?php

declare(strict_types=1);

namespace App\Tests\ContentManagement\Application;

use App\ContentManagement\Application\SiteMenu\AdminUseCases\Output\SiteMenuViewDTO;
use App\ContentManagement\Domain\Entity\SiteMenu;
use App\Shared\Domain\ValueObject\Slug;
use PHPUnit\Framework\TestCase;

final class SiteMenuViewDTOTest extends TestCase
{
    public function testItIncludesTheParentTitle(): void
    {
        $now = new \DateTime();
        $parent = SiteMenu::create('Parent menu', null, true, new Slug('parent-menu'), 0, true, $now);
        $child = SiteMenu::create('Child menu', $parent, true, new Slug('child-menu'), 0, true, $now);

        (new \ReflectionProperty(SiteMenu::class, 'id'))->setValue($parent, 2);
        (new \ReflectionProperty(SiteMenu::class, 'id'))->setValue($child, 1);

        $view = SiteMenuViewDTO::fromSiteMenu($child);

        self::assertSame(2, $view->parentId);
        self::assertSame('Parent menu', $view->parentTitle);
    }
}
