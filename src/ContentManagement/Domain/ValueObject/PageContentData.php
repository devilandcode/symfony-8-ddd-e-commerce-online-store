<?php

declare(strict_types=1);

namespace App\ContentManagement\Domain\ValueObject;

/**
 * Generic presentational payload for a page.
 *
 * The value is a JSON object: string keys at the root, nested lists and maps,
 * and JSON scalars. It does not know which page or which fields it stores.
 */
final readonly class PageContentData
{
    /**
     * @param array<string, mixed> $values
     */
    private function __construct(private array $values)
    {
    }

    /**
     * @param array<mixed, mixed> $values
     */
    public static function fromArray(array $values): self
    {
        self::assertMap($values, true);

        return new self(self::copy($values));
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return self::copy($this->values);
    }

    /**
     * @param array<mixed, mixed> $values
     */
    private static function assertMap(array $values, bool $root): void
    {
        foreach ($values as $key => $value) {
            if ($root && !\is_string($key)) {
                throw new \InvalidArgumentException('Page content keys must be strings.');
            }

            if (!$root && !\is_string($key) && !\is_int($key)) {
                throw new \InvalidArgumentException('Page content keys must be strings or list indexes.');
            }

            if (\is_string($key) && '' === $key) {
                throw new \InvalidArgumentException('Page content keys cannot be empty.');
            }

            self::assertValue($value);
        }
    }

    private static function assertValue(mixed $value): void
    {
        if (\is_array($value)) {
            self::assertMap($value, false);

            return;
        }

        if (null === $value || \is_bool($value) || \is_int($value)) {
            return;
        }

        if (\is_float($value)) {
            if (!is_finite($value)) {
                throw new \InvalidArgumentException('Page content numbers must be finite.');
            }

            return;
        }

        if (\is_string($value)) {
            return;
        }

        throw new \InvalidArgumentException('Page content only accepts JSON scalars, lists, and maps.');
    }

    /**
     * @param array<mixed, mixed> $values
     *
     * @return array<mixed, mixed>
     */
    private static function copy(array $values): array
    {
        $copy = [];
        foreach ($values as $key => $value) {
            $copy[$key] = \is_array($value) ? self::copy($value) : $value;
        }

        return $copy;
    }
}
