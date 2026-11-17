<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Application;

use App\Catalog\Application\Category\AdminUseCases\Output\CategoryViewDTO;
use App\Catalog\Domain\Entity\Category;
use App\Shared\Domain\ValueObject\Slug;
use PHPUnit\Framework\TestCase;

final class CategoryViewDTOTest extends TestCase
{
    public function testItIncludesTheParentTitle(): void
    {
        $now = new \DateTime();
        $parent = Category::create('Parent category', new Slug('parent-category'), null, null, 0, null, null, null, null, 0, true, $now);
        $child = Category::create('Child category', new Slug('child-category'), null, $parent, 1, null, null, null, null, 0, true, $now);

        (new \ReflectionProperty(Category::class, 'id'))->setValue($parent, 2);
        (new \ReflectionProperty(Category::class, 'id'))->setValue($child, 1);

        $view = CategoryViewDTO::fromCategory($child);

        self::assertSame(2, $view->parentId);
        self::assertSame('Parent category', $view->parentTitle);
    }
}
