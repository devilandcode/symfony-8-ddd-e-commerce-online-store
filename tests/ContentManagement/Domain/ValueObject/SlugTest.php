<?php

declare(strict_types=1);

namespace App\Tests\ContentManagement\Domain\ValueObject;

use App\Shared\Domain\ValueObject\Slug;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SlugTest extends TestCase
{
    #[DataProvider('slugExamples')]
    public function testItNormalizesInput(string $input, string $expected): void
    {
        self::assertSame($expected, (new Slug($input))->toString());
    }

    public static function slugExamples(): iterable
    {
        yield 'spaces and uppercase' => ['  Hello World  ', 'hello-world'];
        yield 'repeated separators' => ['Some--Page___Title', 'some-page-title'];
        yield 'unicode lowercase' => ['Crème brûlée', 'crème-brûlée'];
        yield 'digits' => ['Page 2', 'page-2'];
    }

    public function testItRejectsInputWithoutSlugCharacters(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Slug('---');
    }

    public function testItRejectsSlugsLongerThanTheDatabaseLimit(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Slug(str_repeat('a', 256));
    }
}
