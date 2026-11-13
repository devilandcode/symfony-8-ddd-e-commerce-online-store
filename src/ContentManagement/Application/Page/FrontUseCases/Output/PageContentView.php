<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\Page\FrontUseCases\Output;

/**
 * Read-only presentational bag passed to storefront templates.
 */
final readonly class PageContentView implements \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     * @param array<string, mixed> $values
     */
    public function __construct(private array $values)
    {
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
        return $this->values;
    }

    public function offsetExists(mixed $offset): bool
    {
        return \is_string($offset) && \array_key_exists($offset, $this->values);
    }

    public function offsetGet(mixed $offset): mixed
    {
        if (!$this->offsetExists($offset)) {
            throw new \OutOfBoundsException(sprintf('Page content has no "%s" value.', is_scalar($offset) ? (string) $offset : 'value'));
        }

        return $this->values[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \LogicException('Page content is read-only.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new \LogicException('Page content is read-only.');
    }

    public function count(): int
    {
        return \count($this->values);
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->values);
    }
}
