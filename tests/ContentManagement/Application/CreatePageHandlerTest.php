<?php

declare(strict_types=1);

namespace App\Tests\ContentManagement\Application;

use App\ContentManagement\Application\Page\AdminUseCases\Input\PageDataDTO;
use App\ContentManagement\Application\Page\AdminUseCases\CreatePage\CreatePageHandler;
use App\ContentManagement\Application\Page\AdminUseCases\Exception\PageSlugAlreadyInUse;
use App\ContentManagement\Domain\Entity\Page;
use App\ContentManagement\Domain\Repository\PageRepositoryInterface;
use App\Shared\Application\Flusher;
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
        $flusher = $this->createMock(Flusher::class);
        $flusher->expects(self::never())->method('commit');

        $this->expectException(PageSlugAlreadyInUse::class);

        (new CreatePageHandler($repository, $flusher))(new PageDataDTO(
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
