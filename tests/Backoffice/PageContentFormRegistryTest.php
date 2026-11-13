<?php

declare(strict_types=1);

namespace App\Tests\Backoffice;

use App\Backoffice\Infrastructure\Http\Form\PageContent\Exception\PageContentFormNotRegistered;
use App\Backoffice\Infrastructure\Http\Form\PageContent\HomePageContentType;
use App\Backoffice\Infrastructure\Http\Form\PageContent\PageContentFormRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class PageContentFormRegistryTest extends KernelTestCase
{
    public function testRegistryResolvesTheHomepageSlug(): void
    {
        self::bootKernel();

        $registry = static::getContainer()->get(PageContentFormRegistry::class);

        self::assertSame(HomePageContentType::class, $registry->typeFor('home'));
        self::assertTrue($registry->has('home'));
    }

    public function testHasIsFalseWhenNoFormIsRegistered(): void
    {
        self::bootKernel();

        $registry = static::getContainer()->get(PageContentFormRegistry::class);

        self::assertFalse($registry->has('catalog'));
        self::assertFalse($registry->has('contacts'));
    }

    public function testRegistryFailsClearlyForAnUnknownSlug(): void
    {
        self::bootKernel();

        $registry = static::getContainer()->get(PageContentFormRegistry::class);

        $this->expectException(PageContentFormNotRegistered::class);
        $this->expectExceptionMessage('No page content form is registered for the page slug "contacts".');

        $registry->typeFor('contacts');
    }
}
