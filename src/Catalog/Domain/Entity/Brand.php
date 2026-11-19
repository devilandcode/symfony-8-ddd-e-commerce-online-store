<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Entity;

use App\Shared\Domain\ValueObject\Slug;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'brand')]
#[ORM\UniqueConstraint(name: 'uniq_brand_slug', columns: ['slug'])]
class Brand
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'id', type: 'bigint', unique: true)]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $title;

    #[ORM\Embedded(class: Slug::class, columnPrefix: false)]
    private Slug $slug;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $h1 = null;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    private ?string $desc = null;

    /** @var Collection<int, Product> */
    #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'brand')]
    #[ORM\OrderBy(['pos' => \SortDirection::Ascending])]
    private Collection $products;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $seo_title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $seo_description = null;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $pos = 0;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    private bool $is_active = true;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private \DateTime $created_at;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private \DateTime $updated_at;

    private function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public static function create(
        string $title,
        Slug $slug,
        ?string $h1,
        ?string $description,
        ?string $seoTitle,
        ?string $seoDescription,
        int $position,
        bool $isActive,
        \DateTime $now,
    ): self {
        $brand = new self();
        $brand->update(
            $title,
            $slug,
            $h1,
            $description,
            $seoTitle,
            $seoDescription,
            $position,
            $isActive,
            $now,
        );
        $brand->created_at = $now;

        return $brand;
    }

    public function update(
        string $title,
        Slug $slug,
        ?string $h1,
        ?string $description,
        ?string $seoTitle,
        ?string $seoDescription,
        int $position,
        bool $isActive,
        \DateTime $updatedAt,
    ): void {
        $title = trim($title);
        if ('' === $title) {
            throw new \InvalidArgumentException('Brand title cannot be empty.');
        }

        $this->title = $title;
        $this->slug = $slug;
        $this->h1 = $h1;
        $this->desc = $description;
        $this->seo_title = $seoTitle;
        $this->seo_description = $seoDescription;
        $this->pos = $position;
        $this->is_active = $isActive;
        $this->updated_at = $updatedAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSlug(): Slug
    {
        return $this->slug;
    }

    public function getH1(): ?string
    {
        return $this->h1;
    }

    public function getDescription(): ?string
    {
        return $this->desc;
    }

    /** @return list<Product> */
    public function getProducts(): array
    {
        return array_values($this->products->toArray());
    }

    public function getSeoTitle(): ?string
    {
        return $this->seo_title;
    }

    public function getSeoDescription(): ?string
    {
        return $this->seo_description;
    }

    public function getPosition(): int
    {
        return $this->pos;
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updated_at;
    }
}
