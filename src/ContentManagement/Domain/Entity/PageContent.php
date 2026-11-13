<?php

declare(strict_types=1);

namespace App\ContentManagement\Domain\Entity;

use App\ContentManagement\Domain\ValueObject\PageContentData;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(
    name: 'page_content',
    uniqueConstraints: [new ORM\UniqueConstraint(name: 'UNIQ_PAGE_CONTENT_PAGE_ID', columns: ['page_id'])],
)]
class PageContent
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Page::class)]
    #[ORM\JoinColumn(name: 'page_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Page $page;

    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json')]
    private array $payload;

    private function __construct(Page $page, PageContentData $payload)
    {
        $this->page = $page;
        $this->payload = $payload->toArray();
    }

    public static function create(Page $page, PageContentData $payload): self
    {
        return new self($page, $payload);
    }

    public function update(PageContentData $payload): void
    {
        $this->payload = $payload->toArray();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPage(): Page
    {
        return $this->page;
    }

    public function payload(): PageContentData
    {
        return PageContentData::fromArray($this->payload);
    }
}
