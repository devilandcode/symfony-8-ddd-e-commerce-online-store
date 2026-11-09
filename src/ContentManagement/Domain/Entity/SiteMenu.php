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

    #[ORM\Column(name: 'slug', type: 'string', length: 255, nullable: false, unique: true)]
    private string $slugValue;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): Slug
    {
        return new Slug($this->slugValue);
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
