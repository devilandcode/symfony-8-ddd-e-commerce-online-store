<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final readonly class Balance
{
    #[ORM\Column(name: 'balance', type: 'integer', nullable: false, options: ['default' => 0])]
    private int $units;

    public function __construct(int $units)
    {
        if ($units < 0) {
            throw new \InvalidArgumentException('Product balance cannot be negative.');
        }

        $this->units = $units;
    }

    public function getUnits(): int
    {
        return $this->units;
    }
}
