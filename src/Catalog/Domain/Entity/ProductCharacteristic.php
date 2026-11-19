<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'product_characteristic')]
#[ORM\UniqueConstraint(name: 'uniq_product_characteristic', columns: ['product_id', 'characteristic_id'])]
#[ORM\Index(name: 'idx_product_characteristic_characteristic', columns: ['characteristic_id'])]
#[ORM\Index(name: 'IDX_146D77C4584665A', columns: ['product_id'])]
class ProductCharacteristic
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'characteristicAssignments')]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE', foreignKeyName: 'fk_product_characteristic_product')]
    private Product $product;

    #[ORM\ManyToOne(targetEntity: Characteristic::class)]
    #[ORM\JoinColumn(name: 'characteristic_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE', foreignKeyName: 'fk_product_characteristic_characteristic')]
    private Characteristic $characteristic;

    #[ORM\Column(name: 'characteristic_value', type: 'text', nullable: false)]
    private string $value;

    private function __construct()
    {
    }

    public static function create(Product $product, Characteristic $characteristic, string $value): self
    {
        $assignment = new self();
        $assignment->product = $product;
        $assignment->characteristic = $characteristic;
        $assignment->updateValue($value);

        return $assignment;
    }

    public function updateValue(string $value): void
    {
        $value = trim($value);
        if ('' === $value) {
            throw new \InvalidArgumentException('Characteristic value cannot be empty.');
        }

        $this->value = $value;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCharacteristic(): Characteristic
    {
        return $this->characteristic;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
