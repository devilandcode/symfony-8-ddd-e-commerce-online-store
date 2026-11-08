<?php

declare(strict_types=1);

namespace App\Tests\ContentManagement\Application;

use App\ContentManagement\Application\CreatePage\CreatePageHandler;
use App\ContentManagement\Application\Exception\PageSlugAlreadyInUse;
use App\ContentManagement\Application\PageData;
use App\ContentManagement\Domain\Entity\Page;
use App\ContentManagement\Domain\Repository\PageRepositoryInterface;
use App\Shared\Domain\ValueObject\Slug;
use PHPUnit\Framework\TestCase;

final class CreatePageHandlerTest extends TestCase
{
    public function testItRejectsASlugAlreadyUsedByAnotherPage(): void
    {
        $slug = new Slug('Hello World');
        $existingPage = Page::create(
            'Existing page',
            $slug,
            null,
            null,
            null,
            null,
            null,
            0,
            true,
            new \DateTime(),
        );

        $repository = $this->createMock(PageRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findBySlug')
            ->with($slug)
            ->willReturn($existingPage);
        $repository->expects(self::never())->method('save');

        $this->expectException(PageSlugAlreadyInUse::class);

        (new CreatePageHandler($repository))(new PageData(
            'Another page',
            $slug,
            null,
            null,
            null,
            null,
            null,
            0,
            true,
        ));
    }
}
