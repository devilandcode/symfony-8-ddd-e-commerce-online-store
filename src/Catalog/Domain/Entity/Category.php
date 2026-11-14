<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Entity;

use App\Shared\Domain\ValueObject\Slug;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'category')]
#[ORM\Entity]
class Category
{
    #[ORM\Column(name: 'id', type: 'bigint', unique: true)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $title;

    #[ORM\Embedded(class: Slug::class, columnPrefix: false)]
    private Slug $slug;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $h1 = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL', foreignKeyName: 'fk_category_parent')]
    private ?self $parent = null;

    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent')]
    #[ORM\OrderBy(['pos' => \SortDirection::Ascending])]
    private Collection $children;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    private ?string $desc = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content = null;

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
        $this->children = new ArrayCollection();
    }

    public static function create(
        string $title,
        Slug $slug,
        ?string $h1,
        ?self $parent,
        ?string $description,
        ?string $content,
        ?string $seoTitle,
        ?string $seoDescription,
        int $position,
        bool $isActive,
        \DateTime $now,
    ): self {
        $category = new self();
        $category->update(
            $title,
            $slug,
            $h1,
            $parent,
            $description,
            $content,
            $seoTitle,
            $seoDescription,
            $position,
            $isActive,
            $now,
        );
        $category->created_at = $now;

        return $category;
    }

    public function update(
        string $title,
        Slug $slug,
        ?string $h1,
        ?self $parent,
        ?string $description,
        ?string $content,
        ?string $seoTitle,
        ?string $seoDescription,
        int $position,
        bool $isActive,
        \DateTime $updatedAt,
    ): void {
        $title = trim($title);
        if ('' === $title) {
            throw new \InvalidArgumentException('Category title cannot be empty.');
        }

        $this->title = $title;
        $this->slug = $slug;
        $this->h1 = $h1;
        $this->parent = $parent;
        $this->desc = $description;
        $this->content = $content;
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

    public function getSlug(): Slug
    {
        return $this->slug;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getH1(): ?string
    {
        return $this->h1;
    }
    
    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function getDesc(): ?string
    {
        return $this->desc;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getPos(): int
    {
        return $this->pos;
    }

    public function getSeoTitle(): ?string
    {
        return $this->seo_title;
    }

    public function getSeoDescription(): ?string
    {
        return $this->seo_description;
    }

    public function getIsActive(): bool
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
