<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final readonly class OldPrice
{
    #[ORM\Column(name: 'old_price_minor', type: 'bigint', nullable: true)]
    private ?int $minorUnits;

    public function __construct(int $minorUnits)
    {
        if ($minorUnits <= 0) {
            throw new \InvalidArgumentException('Old price must be greater than zero.');
        }

        $this->minorUnits = $minorUnits;
    }

    public function getMinorUnits(): ?int
    {
        return $this->minorUnits;
    }
}
