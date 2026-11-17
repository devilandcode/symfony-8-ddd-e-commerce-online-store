<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Application;

use App\Catalog\Application\Category\FrontUseCases\Exception\CategoryNotFoundException;
use App\Catalog\Application\Category\FrontUseCases\FindChild\FindChildCategoryHandler;
use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Repository\CategoryRepositoryInterface;
use App\Shared\Domain\ValueObject\Slug;
use PHPUnit\Framework\TestCase;

final class FindChildCategoryHandlerTest extends TestCase
{
    public function testItReturnsAChildOfTheRequestedParent(): void
    {
        $now = new \DateTime();
        $parent = Category::create('Parent', new Slug('parent'), null, null, 0, null, null, null, null, 0, true, $now);
        $child = Category::create('Child', new Slug('child'), null, $parent, 1, null, null, null, null, 0, true, $now);
        $repository = $this->createMock(CategoryRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('findBySlug')
            ->with(self::callback(static fn (Slug $slug): bool => 'child' === $slug->toString()))
            ->willReturn($child);

        $result = (new FindChildCategoryHandler($repository))(new Slug('parent'), new Slug('child'));

        self::assertSame('Child', $result->title);
        self::assertSame('parent', $result->parentSlug);
        self::assertSame('Parent', $result->parentTitle);
    }

    public function testItRejectsACategoryWithADifferentParent(): void
    {
        $now = new \DateTime();
        $actualParent = Category::create('Actual parent', new Slug('actual-parent'), null, null, 0, null, null, null, null, 0, true, $now);
        $child = Category::create('Child', new Slug('child'), null, $actualParent, 1, null, null, null, null, 0, true, $now);
        $repository = $this->createStub(CategoryRepositoryInterface::class);
        $repository->method('findBySlug')->willReturn($child);

        $this->expectException(CategoryNotFoundException::class);

        (new FindChildCategoryHandler($repository))(new Slug('different-parent'), new Slug('child'));
    }
}
