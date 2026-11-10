<?php

namespace App\ContentManagement\Domain\Entity;

use App\Shared\Domain\ValueObject\Slug;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'site_menu')]
#[ORM\Entity]
class SiteMenu
{
    #[ORM\Column(name: 'id', type: 'bigint', unique: true)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $title;

    #[ORM\Embedded(class: Slug::class, columnPrefix: false)]
    private Slug $slug;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    private bool $header = true;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $footer = false;

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
        bool $header,
        bool $footer,
        int $position,
        bool $isActive,
        \DateTime $now,
    ): self {
        $menu = new self();
        $menu->title = $title;
        $menu->slug = $slug;
        $menu->header = $header;
        $menu->footer = $footer;
        $menu->pos = $position;
        $menu->is_active = $isActive;
        $menu->created_at = $now;
        $menu->updated_at = $now;

        return $menu;
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

    public function getIsHeader(): bool
    {
        return $this->header;
    }

    public function getIsFooter(): bool
    {
        return $this->footer;
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
