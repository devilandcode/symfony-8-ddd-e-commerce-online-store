<?php

namespace App\ContentManagement\Domain\Entity;

use App\Shared\Domain\ValueObject\Slug;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'site_menu')]
#[ORM\Index(name: 'idx_site_menu_parent', columns: ['parent_id'])]
#[ORM\Entity]
class SiteMenu
{
    #[ORM\Column(name: 'id', type: 'bigint', unique: true)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $title;

    #[ORM\ManyToOne(targetEntity: self::class)]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL', foreignKeyName: 'fk_site_menu_parent')]
    private ?self $parent = null;

    #[ORM\Column(name: 'has_slug', type: 'boolean', nullable: false, options: ['default' => true])]
    private bool $has_slug = true;

    #[ORM\Embedded(class: Slug::class, columnPrefix: false)]
    private Slug $slug;

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
        ?self $parent,
        bool $hasSlug,
        Slug $slug,
        int $position,
        bool $isActive,
        \DateTime $now,
    ): self {
        $menu = new self();
        $menu->update(
            $title,
            $parent,
            $hasSlug,
            $slug,
            $position,
            $isActive,
            $now,
        );

        $menu->created_at = $now;

        return $menu;
    }

    public function update(
        string $title,
        ?self $parent,
        bool $hasSlug,
        Slug $slug,
        int $position,
        bool $isActive,
        \DateTime $updatedAt,
    ): void {
        $title = trim($title);
        if ('' === $title) {
            throw new \InvalidArgumentException('SiteMenu title cannot be empty.');
        }

        if (null !== $parent && ($parent === $this || (null !== $this->id && $parent->getId() === $this->id))) {
            throw new \InvalidArgumentException('A site menu item cannot be its own parent.');
        }

        $this->title = $title;
        $this->parent = $parent;
        $this->has_slug = $hasSlug;
        $this->slug = $slug;
        $this->pos = $position;
        $this->is_active = $isActive;
        $this->updated_at = $updatedAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function hasSlug(): bool
    {
        return $this->has_slug;
    }

    public function getSlug(): Slug
    {
        return $this->slug;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getPos(): int
    {
        return $this->pos;
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
