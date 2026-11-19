<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Entity;

use App\Catalog\Domain\ValueObject\Balance;
use App\Catalog\Domain\ValueObject\OldPrice;
use App\Catalog\Domain\ValueObject\Price;
use App\Shared\Domain\ValueObject\Slug;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'product')]
#[ORM\Index(name: 'IDX_D34A04AD12469DE2', columns: ['category_id'])]
#[ORM\Index(name: 'IDX_D34A04AD44F5D008', columns: ['brand_id'])]
#[ORM\UniqueConstraint(name: 'uniq_product_slug', columns: ['slug'])]
class Product
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

    #[ORM\Embedded(class: Price::class, columnPrefix: false)]
    private Price $price;

    #[ORM\Embedded(class: OldPrice::class, columnPrefix: false)]
    private ?OldPrice $oldPrice = null;

    #[ORM\Embedded(class: Balance::class, columnPrefix: false)]
    private Balance $balance;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'products')]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', nullable: false, onDelete: 'RESTRICT', foreignKeyName: 'fk_product_category')]
    private Category $category;

    #[ORM\ManyToOne(targetEntity: Brand::class, inversedBy: 'products')]
    #[ORM\JoinColumn(name: 'brand_id', referencedColumnName: 'id', nullable: false, onDelete: 'RESTRICT', foreignKeyName: 'fk_product_brand')]
    private Brand $brand;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(name: 'add_images', type: 'json', nullable: true)]
    private ?array $addImages = null;

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
    {}

    public static function create(
        string $title,
        Slug $slug,
        ?string $h1,
        ?string $description,
        Price $price,
        ?OldPrice $oldPrice,
        Balance $balance,
        Category $category,
        Brand $brand,
        ?string $image,
        ?array $addImages,
        ?string $seoTitle,
        ?string $seoDescription,
        int $position,
        bool $isActive,
        \DateTime $now,
    ): self {
        $product = new self();
        $product->update(
            $title,
            $slug,
            $h1,
            $description,
            $price,
            $oldPrice,
            $balance,
            $category,
            $brand,
            $image,
            $addImages,
            $seoTitle,
            $seoDescription,
            $position,
            $isActive,
            $now,
        );
        $product->created_at = $now;

        return $product;
    }

    public function update(
        string $title,
        Slug $slug,
        ?string $h1,
        ?string $description,
        Price $price,
        ?OldPrice $oldPrice,
        Balance $balance,
        Category $category,
        Brand $brand,
        ?string $image,
        ?array $addImages,
        ?string $seoTitle,
        ?string $seoDescription,
        int $position,
        bool $isActive,
        \DateTime $updatedAt,
    ): void {
        $title = trim($title);
        if ('' === $title) {
            throw new \InvalidArgumentException('Product title cannot be empty.');
        }

        $oldPriceMinorUnits = $oldPrice?->getMinorUnits();
        if (null !== $oldPriceMinorUnits && $oldPriceMinorUnits <= $price->getMinorUnits()) {
            throw new \InvalidArgumentException('Old price must be greater than the current price.');
        }

        $this->title = $title;
        $this->slug = $slug;
        $this->h1 = $h1;
        $this->desc = $description;
        $this->price = $price;
        $this->oldPrice = $oldPrice;
        $this->balance = $balance;
        $this->category = $category;
        $this->brand = $brand;
        $this->image = $image;
        $this->addImages = $addImages;
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

    public function getDescription(): ?string
    {
        return $this->desc;
    }

    public function getPrice(): Price
    {
        return $this->price;
    }

    public function getOldPrice(): ?OldPrice
    {
        return null === $this->oldPrice?->getMinorUnits() ? null : $this->oldPrice;
    }

    public function getBalance(): Balance
    {
        return $this->balance;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function getBrand(): Brand
    {
        return $this->brand;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function getAdditionalImages(): ?array
    {
        return $this->addImages;
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
