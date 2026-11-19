<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(
    name: 'category_characteristic',
    uniqueConstraints: [new ORM\UniqueConstraint(name: 'uniq_category_characteristic', columns: ['category_id', 'characteristic_id'])],
    indexes: [new ORM\Index(name: 'idx_category_characteristic_characteristic', columns: ['characteristic_id'])],
)]
class CategoryCharacteristic
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'characteristics')]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Category $category;

    #[ORM\ManyToOne(targetEntity: Characteristic::class)]
    #[ORM\JoinColumn(name: 'characteristic_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Characteristic $characteristic;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $in_filter;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    private bool $is_active;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private \DateTime $created_at;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private \DateTime $updated_at;

    private function __construct()
    {
    }

    public static function create(
        Category $category,
        Characteristic $characteristic,
        bool $isActive,
        bool $inFilter,
        \DateTime $now,
    ): self {
        $assignment = new self();
        $assignment->category = $category;
        $assignment->characteristic = $characteristic;
        $assignment->update($isActive, $inFilter, $now);
        $assignment->created_at = $now;

        return $assignment;
    }

    public function update(bool $isActive, bool $inFilter, \DateTime $updatedAt): void
    {
        $this->is_active = $isActive;
        $this->in_filter = $inFilter;
        $this->updated_at = $updatedAt;
    }

    public function getCharacteristic(): Characteristic
    {
        return $this->characteristic;
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function isInFilter(): bool
    {
        return $this->in_filter;
    }
}
