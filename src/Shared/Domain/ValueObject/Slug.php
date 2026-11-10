<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final readonly class Slug implements \Stringable
{
    #[ORM\Column(name: 'slug', type: 'string', length: 255, nullable: false, unique: true)]
    private string $value;

    public function __construct(string $value)
    {
        $normalized = mb_strtolower(trim($value), 'UTF-8');
        $normalized = preg_replace('/[^\p{L}\p{N}]+/u', '-', $normalized);

        if (null === $normalized) {
            throw new \InvalidArgumentException('Slug must be valid UTF-8 text.');
        }

        $normalized = trim($normalized, '-');

        if ('' === $normalized) {
            throw new \InvalidArgumentException('Slug cannot be empty.');
        }

        if (mb_strlen($normalized, 'UTF-8') > 255) {
            throw new \InvalidArgumentException('Slug cannot exceed 255 characters.');
        }

        $this->value = $normalized;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
